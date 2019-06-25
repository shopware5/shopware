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
use Shopware\Components\OptinServiceInterface;
use Shopware\Components\Theme;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Shop\Template;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Shopware_Controllers_Backend_Theme extends Shopware_Controllers_Backend_Application implements CSRFWhitelistAware
{
    /**
     * Model which handled through this controller
     *
     * @var string
     */
    protected $model = Template::class;

    /**
     * SQL alias for the internal query builder
     *
     * @var string
     */
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
     */
    public function assignAction()
    {
        // Reset preview template
        $this->resetPreviewSessionAction();

        $this->get('theme_service')->assignShopTemplate(
            $this->Request()->getParam('shopId'),
            $this->Request()->getParam('themeId')
        );

        $this->View()->assign('success', true);
    }

    /**
     * Starts a template preview for the passed theme
     * and shop id.
     */
    public function previewAction()
    {
        $themeId = $this->Request()->getParam('themeId');
        $shopId = $this->Request()->getParam('shopId');

        /** @var Template $theme */
        $theme = $this->getRepository()->find($themeId);

        /** @var Shop $shop */
        $shop = $this->getManager()->getRepository(Shop::class)->getActiveById($shopId);
        $this->get('shopware.components.shop_registration_service')->registerShop($shop);

        $session = $this->get('session');

        $session->template = $theme->getTemplate();
        $session->Admin = true;

        if (!$this->Request()->isXmlHttpRequest()) {
            $this->get('events')->notify('Shopware_Theme_Preview_Starts', [
                'session' => Shopware()->Session(),
                'shop' => $shop,
                'theme' => $theme,
            ]);

            $hash = $this->container->get('shopware.components.optin_service')->add(OptinServiceInterface::TYPE_THEME_PREVIEW, 300, [
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
     * Resets the template variable within the shop session
     * for the passed shop id.
     */
    public function resetPreviewSessionAction()
    {
        $shopId = $this->Request()->getParam('shopId');

        if (empty($shopId)) {
            return;
        }

        /** @var Shop $shop */
        $shop = $this->getManager()->getRepository(Shop::class)->getActiveById(
            $shopId
        );

        if (!$shop instanceof Shop) {
            return;
        }

        $this->get('shopware.components.shop_registration_service')->registerShop($shop);

        Shopware()->Session()->offsetSet('template', null);
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
            throw new Exception(
                'A theme with that name already exists'
            );
        }

        $parent = null;
        if ($parentId) {
            $parent = $this->getRepository()->find($parentId);

            if (!$parent instanceof Template) {
                throw new Exception(sprintf(
                    'Shop template by id %s not found',
                    $parentId
                ));
            }
        }

        $this->container->get('theme_generator')->generateTheme(
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
        $this->container->get('theme_installer')->synchronize();

        parent::listAction();
    }

    /**
     * Used for the configuration window.
     * Returns all configuration sets for the passed
     * template id.
     */
    public function getConfigSetsAction()
    {
        $template = $this->Request()->getParam('templateId');
        $template = $this->getRepository()->find($template);

        $this->View()->assign([
            'success' => true,
            'data' => $this->get('theme_service')->getConfigSets($template),
        ]);
    }

    /**
     * Saves the passed theme configuration.
     *
     * @param array $data
     *
     * @return array
     */
    public function save($data)
    {
        $theme = $this->getRepository()->find($data['id']);

        $this->get('theme_service')->saveConfig(
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
     */
    public function uploadAction()
    {
        /** @var UploadedFile $file */
        $file = Symfony\Component\HttpFoundation\Request::createFromGlobals()->files->get('fileId');
        $system = new Filesystem();

        if (strtolower($file->getClientOriginalExtension()) !== 'zip') {
            $name = $file->getClientOriginalName();

            $system->remove($file->getPathname());

            throw new Exception(sprintf(
                'Uploaded file %s is no zip file',
                $name
            ));
        }
        $targetDirectory = $this->container->get('theme_path_resolver')->getFrontendThemeDirectory();

        if (!is_writable($targetDirectory)) {
            return $this->View()->assign([
                'success' => false,
                'error' => sprintf("Target Directory %s isn't writable", $targetDirectory),
            ]);
        }

        $this->unzip($file, $targetDirectory);

        $system->remove($file->getPathname());

        $this->View()->assign('success', true);
    }

    public function loadSettingsAction()
    {
        $this->View()->assign([
            'success' => true,
            'data' => $this->container->get('theme_service')->getSystemConfiguration(),
        ]);
    }

    public function saveSettingsAction()
    {
        $this->View()->assign([
            'success' => true,
            'data' => $this->container->get('theme_service')->saveSystemConfiguration(
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
     *
     * @return array
     */
    protected function getAdditionalDetailData(array $data)
    {
        /** @var Template $template */
        $template = $this->getRepository()->find($data['id']);

        /** @var Shop $shop */
        $shop = $this->getManager()->find(
            Shop::class,
            $this->Request()->getParam('shopId')
        );

        $data['hasConfigSet'] = $this->hasTemplateConfigSet($template);

        $data['configLayout'] = $this->container->get('theme_service')->getLayout(
            $template,
            $shop
        );

        $data['themeInfo'] = $this->getThemeInfo($template);

        return $this->get('events')->filter('Shopware_Theme_Detail_Loaded', $data, [
            'shop' => $shop,
            'template' => $template,
        ]);
    }

    /**
     * The getList function returns an array of the configured class model.
     * The listing query created in the getListQuery function.
     * The pagination of the listing is handled inside this function.
     *
     * @param int   $offset
     * @param int   $limit
     * @param array $sort        Contains an array of Ext JS sort conditions
     * @param array $filter      Contains an array of Ext JS filters
     * @param array $wholeParams Contains all passed request parameters
     *
     * @return array
     */
    protected function getList($offset, $limit, $sort = [], $filter = [], array $wholeParams = [])
    {
        if (!isset($wholeParams['shopId'])) {
            $wholeParams['shopId'] = $this->getDefaultShopId();
        }

        $data = parent::getList(null, null, $sort, $filter, $wholeParams);

        /** @var Shop $shop */
        $shop = $this->getManager()->find(Shop::class, $wholeParams['shopId']);

        foreach ($data['data'] as &$theme) {
            /** @var Template $instance */
            $instance = $this->getRepository()->find($theme['id']);

            $theme['screen'] = $this->container->get('theme_util')->getPreviewImage(
                $instance
            );

            $theme['path'] = $this->container->get('theme_path_resolver')->getDirectory(
                $instance
            );

            $theme = $this->get('theme_service')->translateTheme(
                $instance,
                $theme
            );

            if ($shop instanceof Shop && $shop->getTemplate() instanceof Template) {
                $theme['enabled'] = ($theme['id'] === $shop->getTemplate()->getId());
            }
        }

        $data = $this->get('events')->filter('Shopware_Theme_Listing_Loaded', $data, [
            'shop' => $shop,
        ]);

        return $data;
    }

    /**
     * Override of the Application controller to select the template configuration.
     *
     * @return \Shopware\Components\Model\QueryBuilder
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
     * @param string $targetDirectory
     *
     * @throws Exception
     */
    private function unzip(UploadedFile $file, $targetDirectory)
    {
        $filePath = $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename();
        $zipUtils = ZipUtils::openZip($filePath);
        $zipUtils->extractTo($targetDirectory);
    }

    /**
     * Helper function which checks if the passed template
     * or the inheritance templates has configuration sets.
     *
     * @return bool
     */
    private function hasTemplateConfigSet(Template $template)
    {
        /** @var Theme $theme */
        $theme = $this->get('theme_util')->getThemeByTemplate($template);

        if ($template->getConfigSets()->count() > 0) {
            return true;
        } elseif ($theme->useInheritanceConfig() && $template->getParent() instanceof Template) {
            return $this->hasTemplateConfigSet($template->getParent());
        }

        return false;
    }

    /**
     * Returns the id of the default shop.
     *
     * @return string
     */
    private function getDefaultShopId()
    {
        return Shopware()->Db()->fetchOne(
            'SELECT id FROM s_core_shops WHERE `default` = 1'
        );
    }

    /**
     * @return string|null
     */
    private function getThemeInfo(Template $template)
    {
        $user = $this->get('auth')->getIdentity();
        /** @var Locale $locale */
        $locale = $user->locale;
        $localeCode = $locale->getLocale();

        $path = $this->container->get('theme_path_resolver')->getDirectory($template);

        $languagePath = $path . '/info/' . $localeCode . '.html';
        if (file_exists($languagePath)) {
            return file_get_contents($languagePath);
        }

        if (file_exists($path . '/info/en_GB.html')) {
            return file_get_contents($path . '/info/en_GB.html');
        }

        return null;
    }
}
