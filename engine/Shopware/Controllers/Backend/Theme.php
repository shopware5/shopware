<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

use Shopware\Bundle\PluginInstallerBundle\Service\ZipUtils;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Components\Model\Exception\ModelNotFoundException;
use Shopware\Components\OptinServiceInterface;
use Shopware\Components\ShopRegistrationServiceInterface;
use Shopware\Components\Theme;
use Shopware\Components\Theme\Installer;
use Shopware\Components\Theme\PathResolver;
use Shopware\Components\Theme\Service;
use Shopware\Components\Theme\Util;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Shop\Template;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @extends Shopware_Controllers_Backend_Application<Template>
 */
class Shopware_Controllers_Backend_Theme extends Shopware_Controllers_Backend_Application implements CSRFWhitelistAware
{
    protected $model = Template::class;

    protected $alias = 'template';

    /**
     * {@inheritdoc}
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'preview',
        ];
    }

    /**
     * Controller action which called to assign a shop template.
     *
     * @return void
     */
    public function assignAction()
    {
        // Reset preview template
        $this->resetPreviewSessionAction();

        $this->get(Service::class)->assignShopTemplate(
            $this->Request()->getParam('shopId'),
            $this->Request()->getParam('themeId')
        );

        $this->View()->assign('success', true);
    }

    /**
     * Starts a template preview for the passed theme and shop id.
     *
     * @return void
     */
    public function previewAction()
    {
        $themeId = $this->Request()->getParam('themeId');
        $shopId = $this->Request()->getParam('shopId');

        $theme = $this->getRepository()->find($themeId);
        if (!$theme instanceof Template) {
            throw new ModelNotFoundException(Template::class, $themeId);
        }

        $shop = $this->getManager()->getRepository(Shop::class)->getActiveById($shopId);

        session_write_close();

        $this->get(ShopRegistrationServiceInterface::class)->registerShop($shop);

        $session = $this->get('session');

        $session->template = $theme->getTemplate();
        $session->Admin = true;

        if (!$this->Request()->isXmlHttpRequest()) {
            $this->get('events')->notify('Shopware_Theme_Preview_Starts', [
                'session' => Shopware()->Session(),
                'shop' => $shop,
                'theme' => $theme,
            ]);

            $hash = $this->container->get(OptinServiceInterface::class)->add(OptinServiceInterface::TYPE_THEME_PREVIEW, 300, [
                'sessionName' => session_name(),
                'sessionValue' => $session->get('sessionId'),
            ]);

            $url = $this->Front()->Router()->assemble([
                'module' => 'frontend',
                'controller' => 'index',
                'themeHash' => $hash,
            ]);

            $this->redirect($url);
        }
    }

    /**
     * Resets the template variable within the shop session for the passed shop id.
     *
     * @return void
     */
    public function resetPreviewSessionAction()
    {
        $shopId = $this->Request()->getParam('shopId');

        if (empty($shopId)) {
            return;
        }

        $shop = $this->getManager()->getRepository(Shop::class)->getActiveById($shopId);

        if (!$shop instanceof Shop) {
            return;
        }

        session_write_close();

        $this->get(ShopRegistrationServiceInterface::class)->registerShop($shop);

        $this->get('session')->offsetSet('template', null);
    }

    /**
     * Used to generate a new theme.
     *
     * @throws Exception
     */
    public function createAction()
    {
        $template = $this->Request()->getParam('template');
        $name = $this->Request()->getParam('name');
        $parentId = $this->Request()->getParam('parentId');

        if (empty($template)) {
            throw new Exception('Each theme requires a defined source code name!');
        }
        if (empty($name)) {
            throw new Exception('Each theme requires a defined readable name!');
        }

        if ($this->getRepository()->findOneByTemplate($template)) {
            throw new Exception('A theme with that name already exists');
        }

        $parent = null;
        if ($parentId) {
            $parent = $this->getRepository()->find($parentId);

            if (!$parent instanceof Template) {
                throw new Exception(sprintf('Shop template by id %s not found', $parentId));
            }
        }

        $this->container->get(\Shopware\Components\Theme\Generator::class)->generateTheme(
            $this->Request()->getParams(),
            $parent
        );

        $this->View()->assign('success', true);
    }

    /**
     * Override of the application controller
     * to trigger the theme and template registration when the
     * list should be displayed.
     */
    public function listAction()
    {
        $this->container->get(Installer::class)->synchronize();

        parent::listAction();
    }

    /**
     * Used for the configuration window.
     * Returns all configuration sets for the passed
     * template id.
     *
     * @return void
     */
    public function getConfigSetsAction()
    {
        $template = $this->Request()->getParam('templateId');
        $template = $this->getRepository()->find($template);

        $this->View()->assign([
            'success' => true,
            'data' => $this->get(Service::class)->getConfigSets($template),
        ]);
    }

    /**
     * Saves the passed theme configuration.
     */
    public function save($data)
    {
        $theme = $this->getRepository()->find($data['id']);

        $this->get(Service::class)->saveConfig(
            $theme,
            $data['values']
        );

        return ['success' => true];
    }

    /**
     * Controller action which is used to upload a theme zip file
     * and extract it into the engine\Shopware\Themes folder.
     *
     * @throws Exception
     *
     * @return void
     */
    public function uploadAction()
    {
        $file = Symfony\Component\HttpFoundation\Request::createFromGlobals()->files->get('fileId');
        $system = new Filesystem();

        if (strtolower($file->getClientOriginalExtension()) !== 'zip') {
            $name = $file->getClientOriginalName();

            $system->remove($file->getPathname());

            throw new Exception(sprintf('Uploaded file %s is no zip file', $name));
        }
        $targetDirectory = $this->container->get(PathResolver::class)->getFrontendThemeDirectory();

        if (!is_writable($targetDirectory)) {
            $this->View()->assign([
                'success' => false,
                'error' => sprintf("Target Directory %s isn't writable", $targetDirectory),
            ]);

            return;
        }

        $this->unzip($file, $targetDirectory);

        $system->remove($file->getPathname());

        $this->View()->assign('success', true);
    }

    /**
     * @return void
     */
    public function loadSettingsAction()
    {
        $this->View()->assign([
            'success' => true,
            'data' => $this->container->get(Service::class)->getSystemConfiguration(),
        ]);
    }

    /**
     * @return void
     */
    public function saveSettingsAction()
    {
        $this->View()->assign([
            'success' => true,
            'data' => $this->container->get(Service::class)->saveSystemConfiguration(
                $this->Request()->getParams()
            ),
        ]);
    }

    protected function initAcl()
    {
        // read
        $this->addAclPermission('assign', 'read', 'Insufficient Permissions');
        $this->addAclPermission('list', 'read', 'Insufficient Permissions');

        // preview
        $this->addAclPermission('preview', 'preview', 'Insufficient Permissions');

        // changeTheme
        $this->addAclPermission('assign', 'changeTheme', 'Insufficient Permissions');

        // createTheme
        $this->addAclPermission('create', 'createTheme', 'Insufficient Permissions');

        // uploadTheme
        $this->addAclPermission('upload', 'uploadTheme', 'Insufficient Permissions');

        // configureTheme
        $this->addAclPermission('getConfigSets', 'configureTheme', 'Insufficient Permissions');

        // configureSystem
        $this->addAclPermission('loadSettings', 'configureSystem', 'Insufficient Permissions');
        $this->addAclPermission('saveSettings', 'configureSystem', 'Insufficient Permissions');
    }

    /**
     * Override to get all snippet definitions for the loaded theme configuration.
     */
    protected function getAdditionalDetailData(array $data)
    {
        $template = $this->getRepository()->find($data['id']);
        if (!$template instanceof Template) {
            throw new ModelNotFoundException(Template::class, $data['id']);
        }

        $shop = $this->getManager()->find(
            Shop::class,
            $this->Request()->getParam('shopId')
        );

        $data['hasConfigSet'] = $this->hasTemplateConfigSet($template);

        $data['configLayout'] = $this->container->get(Service::class)->getLayout(
            $template,
            $shop
        );

        $data['themeInfo'] = $this->getThemeInfo($template);

        return $this->get('events')->filter('Shopware_Theme_Detail_Loaded', $data, [
            'shop' => $shop,
            'template' => $template,
        ]);
    }

    protected function getList($offset, $limit, $sort = [], $filter = [], array $wholeParams = [])
    {
        if (!isset($wholeParams['shopId'])) {
            $wholeParams['shopId'] = $this->getDefaultShopId();
        }

        $data = parent::getList(null, null, $sort, $filter, $wholeParams);

        $shop = $this->getManager()->find(Shop::class, $wholeParams['shopId']);

        foreach ($data['data'] as &$theme) {
            $instance = $this->getRepository()->find($theme['id']);
            if (!$instance instanceof Template) {
                continue;
            }

            $theme['screen'] = $this->container->get(Util::class)->getPreviewImage($instance);

            $theme['path'] = $this->container->get(PathResolver::class)->getDirectory($instance);

            $theme = $this->get(Service::class)->translateTheme($instance, $theme);

            if ($shop instanceof Shop && $shop->getTemplate() instanceof Template) {
                $theme['enabled'] = $theme['id'] === $shop->getTemplate()->getId();
            }
        }

        return $this->get('events')->filter('Shopware_Theme_Listing_Loaded', $data, [
            'shop' => $shop,
        ]);
    }

    /**
     * Override of the Application controller to select the template configuration.
     */
    protected function getListQuery()
    {
        $builder = $this->getManager()->createQueryBuilder();
        $fields = $this->getModelFields($this->model, $this->alias);

        $builder->select(array_column($fields, 'alias'));
        $builder->from($this->model, $this->alias);

        $builder->addSelect('COUNT(elements.id) as hasConfig')
            ->leftJoin('template.elements', 'elements')
            ->orderBy('template.version', 'DESC')
            ->addOrderBy('template.name')
            ->groupBy('template.id');

        return $this->get('events')->filter('Shopware_Theme_Listing_Query_Created', $builder);
    }

    /**
     * Helper function to decompress zip files.
     *
     * @throws Exception
     */
    private function unzip(UploadedFile $file, string $targetDirectory): void
    {
        $filePath = $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename();
        $zipUtils = ZipUtils::openZip($filePath);
        $zipUtils->extractTo($targetDirectory);
    }

    /**
     * Helper function which checks if the passed template
     * or the inheritance templates has configuration sets.
     */
    private function hasTemplateConfigSet(Template $template): bool
    {
        $theme = $this->get(Util::class)->getThemeByTemplate($template);

        if ($template->getConfigSets()->count() > 0) {
            return true;
        }

        if ($theme->useInheritanceConfig() && $template->getParent() instanceof Template) {
            return $this->hasTemplateConfigSet($template->getParent());
        }

        return false;
    }

    /**
     * Returns the id of the default shop.
     */
    private function getDefaultShopId(): int
    {
        return (int) Shopware()->Db()->fetchOne(
            'SELECT id FROM s_core_shops WHERE `default` = 1'
        );
    }

    private function getThemeInfo(Template $template): ?string
    {
        $localeCode = $this->get('auth')->getIdentity()->locale->getLocale();

        $path = $this->container->get(PathResolver::class)->getDirectory($template);

        $languagePath = $path . '/info/' . $localeCode . '.html';
        if (file_exists($languagePath)) {
            $contents = file_get_contents($languagePath);
            if (!\is_string($contents)) {
                return null;
            }

            return $contents;
        }

        if (file_exists($path . '/info/en_GB.html')) {
            $contents = file_get_contents($path . '/info/en_GB.html');
            if (!\is_string($contents)) {
                return null;
            }

            return $contents;
        }

        return null;
    }
}
