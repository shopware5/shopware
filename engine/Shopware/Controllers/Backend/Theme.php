<?php
/**
 * Shopware 4
 * Copyright Â© shopware AG
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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\AbstractQuery;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Shop\Template;
use Shopware\Models\Shop\TemplateConfig;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Backend controller for the theme manager 2.0
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_Theme extends Shopware_Controllers_Backend_Application
{
    /**
     * Model which handled through this controller
     * @var string
     */
    protected $model = 'Shopware\Models\Shop\Template';

    /**
     * SQL alias for the internal query builder
     * @var string
     */
    protected $alias = 'template';

    /**
     * Controller action which called to assign a shop template.
     */
    public function assignAction()
    {
        $this->View()->assign(
            $this->assign(
                $this->Request()->getParam('shopId', null),
                $this->Request()->getParam('themeId', null)
            )
        );
    }

    /**
     * Starts a template preview for the passed theme
     * and shop id.
     */
    public function previewAction()
    {
        $themeId = $this->Request()->getParam('themeId');

        $shopId = $this->Request()->getParam('shopId');

        /**@var $theme Template */
        $theme = $this->getRepository()->find($themeId);

        /** @var $shop \Shopware\Models\Shop\Shop */
        $shop = $this->getManager()->getRepository('Shopware\Models\Shop\Shop')->getActiveById($shopId);
        $shop->registerResources(Shopware()->Bootstrap());

        Shopware()->Session()->template = $theme->getTemplate();
        Shopware()->Session()->Admin = true;

        if (!$this->Request()->isXmlHttpRequest()) {
            $url = $this->Front()->Router()->assemble(array(
                'module' => 'frontend',
                'controller' => 'index',
                'appendSession' => true,
            ));
            $this->redirect($url);
        }
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

        $this->container->get('theme_factory')->generateTheme(
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
        $this->container->get('theme_manager')->registerTemplates();
        $this->container->get('theme_manager')->registerThemes();

        parent::listAction();
    }

    /**
     * Controller action which is used to upload a theme zip file
     * and extract it into the engine\Shopware\Themes folder.
     *
     * @throws Exception
     */
    public function uploadAction()
    {
        /**@var $file UploadedFile*/
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

        $this->unzip($file, $this->container->get('theme_manager')->getDefaultThemeDirectory());

        $system->remove($file->getPathname());

        $this->View()->assign('success', true);
    }

    /**
     * Helper function to decompress zip files.
     * @param UploadedFile $file
     * @param $targetDirectory
     */
    private function unzip(UploadedFile $file, $targetDirectory)
    {
        $filter = new \Zend_Filter_Decompress(array(
            'adapter' => $file->getClientOriginalExtension(),
            'options' => array('target' => $targetDirectory)
        ));

        $filter->filter(
            $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename()
        );
    }

    /**
     * Override of the Application controller to select all template associations.
     *
     * @param $id
     * @return \Doctrine\ORM\QueryBuilder|\Shopware\Components\Model\QueryBuilder
     */
    protected function getDetailQuery($id)
    {
        $builder = parent::getDetailQuery($id);

        return $builder;
    }

    /**
     * @param Template $template
     * @return Enlight_Components_Snippet_Namespace
     */
    private function getSnippetNamespace(Template $template)
    {
        return $this->container->get('snippets')->getNamespace(
            $this->container->get('theme_manager')->getSnippetNamespace($template) . 'backend/config'
        );
    }

    /**
     * Override to get all snippet definitions for the loaded theme configuration.
     *
     * @param array $data
     * @return array
     */
    protected function getAdditionalDetailData(array $data)
    {
        /**@var $template Template*/
        $template = $this->getRepository()->find($data['id']);

        /**@var $shop Shop*/
        $shop = $this->getManager()->find(
            'Shopware\Models\Shop\Shop',
            $this->Request()->getParam('shopId')
        );

        $namespace = $this->getSnippetNamespace($template);

        $namespace->read();

        $data['configLayout'] = $this->getConfigLayout(
            $template,
            $shop,
            null,
            $namespace
        );

        return $data;
    }

    /**
     * @param Template $template
     * @param Shopware\Models\Shop\Shop $shop
     * @param null $parentId
     * @param Enlight_Components_Snippet_Namespace $namespace
     * @return array
     */
    private function getConfigLayout(
        Template $template,
        Shop $shop,
        $parentId = null,
        Enlight_Components_Snippet_Namespace $namespace)
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(array('layout', 'elements', 'values'))
            ->from('Shopware\Models\Shop\TemplateConfig\Layout', 'layout')
            ->leftJoin('layout.elements', 'elements')
            ->leftJoin('elements.values', 'values', 'WITH', 'values.shopId = :shopId')
            ->where('layout.templateId = :templateId')
            ->setParameter('templateId', $template->getId())
            ->setParameter('shopId', $shop->getId());

        if ($parentId == null) {
            $builder->andWhere('layout.parentId IS NULL');
        } else {
            $builder->andWhere('layout.parentId = :parentId')
                ->setParameter('parentId', $parentId);
        }

        $layout = $builder->getQuery()->getArrayResult();

        foreach($layout as &$container) {
            $container = $this->translateContainer(
                $container,
                $namespace
            );

            $container['children'] = $this->getConfigLayout(
                $template,
                $shop,
                $container['id'],
                $namespace
            );
        }

        return $layout;
    }

    /**
     *
     */
    public function getConfigSetsAction()
    {
        $template = $this->Request()->getParam('templateId');
        $template = $this->getRepository()->find($template);

        $this->View()->assign(array(
            'succes' => true,
            'data' => $this->getConfigSets($template)
        ));
    }

    /**
     * @param Template $template
     * @return array
     */
    private function getConfigSets(Template $template)
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(array('template', 'sets'))
            ->from('Shopware\Models\Shop\Template', 'template')
            ->innerJoin('template.configSets', 'sets')
            ->where('sets.templateId = :templateId')
            ->setParameter('templateId', $template->getId());

        $sets = $builder->getQuery()->getArrayResult();

        $sets[0]['screen'] = $this->container->get('theme_manager')->getThemeImage($template);

        if ($template->getParent() instanceof Template) {
            $sets = array_merge(
                $sets,
                $this->getConfigSets($template->getParent())
            );
        }
        return $sets;
    }

    /**
     * Saves the passed theme configuration.
     *
     * @param $data
     * @return array|void
     */
    public function save($data)
    {
        $theme = $this->getRepository()->find($data['id']);

        /**@var $theme Template */
        foreach ($data['values'] as $valueData) {

            $element = $this->getElementByName(
                $theme->getElements(),
                $valueData['elementName']
            );

            if (!($element instanceof TemplateConfig\Element)) {
                continue;
            }

            $value = $this->getElementShopValue(
                $element->getValues(),
                $valueData['shopId']
            );

            /**@var $shop Shop*/
            $shop = $this->getManager()->getReference(
                'Shopware\Models\Shop\Shop',
                $valueData['shopId']
            );

            $value->setShop($shop);
            $value->setElement($element);

            $value->setValue($valueData['value']);
        }

        $this->getManager()->flush();
    }

    /**
     * The getList function returns an array of the configured class model.
     * The listing query created in the getListQuery function.
     * The pagination of the listing is handled inside this function.
     *
     * @param int $offset
     * @param int $limit
     * @param array $sort Contains an array of Ext JS sort conditions
     * @param array $filter Contains an array of Ext JS filters
     * @param array $wholeParams Contains all passed request parameters
     * @return array
     */
    protected function getList($offset, $limit, $sort = array(), $filter = array(), array $wholeParams = array())
    {
        if (!isset($wholeParams['shopId'])) {
            $wholeParams['shopId'] = $this->getDefaultShopId();
        }

        $data = parent::getList(null, null, $sort, $filter, $wholeParams);

        $template = $this->getShopTemplate($wholeParams['shopId']);

        if (!$template instanceof Template) {
            return $data;
        }

        foreach ($data['data'] as &$theme) {
            /**@var $instance Template*/
            $instance = $this->getRepository()->find($theme['id']);

            if ($theme['version'] < 3) {
                $theme['screen'] = $this->container->get('theme_manager')->getTemplateImage($instance);
                $theme['path'] = $this->container->get('theme_manager')->getTemplateDirectory($instance);
            } else {
                $namespace = $this->getSnippetNamespace($instance);
                $namespace->read();

                $theme['screen'] = $this->container->get('theme_manager')->getThemeImage($instance);
                $theme['path'] = $this->container->get('theme_manager')->getThemeDirectory($instance);

                $theme['name'] = $namespace->get('theme_name', $theme['name']);
                $theme['description'] = $namespace->get('theme_description', $theme['description']);
            }
            $theme['enabled'] = ($theme['id'] === $template->getId());
        }

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
            ->groupBy('template.id');

        return $builder;
    }

    /**
     * Assigns the passed theme (identified over the primary key)
     * to the passed shop (identified over the shop primary key)
     *
     * @param $shopId
     * @param $themeId
     * @return array
     */
    protected function assign($shopId, $themeId)
    {
        /**@var $shop Shop */
        $shop = $this->getManager()->find('Shopware\Models\Shop\Shop', $shopId);

        /**@var $theme Template */
        $theme = $this->getManager()->find('Shopware\Models\Shop\Template', $themeId);

        $shop->setTemplate($theme);

        $this->getManager()->flush();

        return array('success' => true);
    }

    /**
     * Returns the current selected template for the passed shop id.
     *
     * @param $shopId
     * @return Template
     */
    protected function getShopTemplate($shopId)
    {
        $builder = $this->getRepository()->createQueryBuilder('template');
        $builder->innerJoin('template.shops', 'shops')
            ->where('shops.id = :shopId')
            ->setParameter('shopId', $shopId);

        return $builder->getQuery()->getOneOrNullResult(
            AbstractQuery::HYDRATE_OBJECT
        );
    }

    /**
     * Returns the id of the default shop.
     * @return string
     */
    private function getDefaultShopId()
    {
        return Shopware()->Db()->fetchOne(
            'SELECT id FROM s_core_shops WHERE `default` = 1'
        );
    }

    /**
     * Helper function which checks if the element name is already exists in the
     * passed collection of config elements.
     *
     * @param $collection
     * @param $name
     * @return null|TemplateConfig\Element
     */
    private function getElementByName($collection, $name)
    {
        /**@var $element TemplateConfig\Element */
        foreach ($collection as $element) {
            if ($element->getName() == $name) {
                return $element;
            }
        }
        return null;
    }

    /**
     * Helper function to get the theme configuration value of the passed
     * value collection.
     * If no shop value exist, the function creates a new value object.
     *
     * @param ArrayCollection $collection
     * @param $shopId
     * @return TemplateConfig\Value
     */
    private function getElementShopValue(ArrayCollection $collection, $shopId)
    {
        /**@var $value TemplateConfig\Value */
        foreach ($collection as $value) {
            if ($value->getShop()->getId() == $shopId) {
                return $value;
            }
        }
        $value = new TemplateConfig\Value();
        $collection->add($value);
        return $value;
    }

    /**
     * @param array $container
     * @param Enlight_Components_Snippet_Namespace $namespace
     * @return array
     */
    private function translateContainer(array $container, Enlight_Components_Snippet_Namespace $namespace)
    {
        foreach($container['elements'] as &$element) {
            $element['fieldLabel'] = $this->convertSnippet(
                $element['fieldLabel'],
                $namespace
            );
            $element['supportText'] = $this->convertSnippet(
                $element['supportText'],
                $namespace
            );
        }

        if (isset($container['title'])) {
            $container['title'] = $this->convertSnippet(
                $container['title'],
                $namespace
            );
        }
        return $container;
    }

    /**
     * Helper function to check, convert and load the translation for
     * the passed value.
     *
     * @param $snippet
     * @param Enlight_Components_Snippet_Namespace $namespace
     * @return mixed
     */
    private function convertSnippet($snippet, Enlight_Components_Snippet_Namespace $namespace)
    {
        if (!$this->isSnippet($snippet)) {
            return $snippet;
        }

        return $namespace->get(
            $this->getSnippetName($snippet)
        );
    }

    /**
     * Checks if the passed value match the snippet pattern.
     *
     * @param $value
     * @return bool
     */
    private function isSnippet($value)
    {
        return (bool) (substr($value, -2) == '__'
            && substr($value, 0, 2) == '__');
    }

    /**
     * Helper function to remove the snippet pattern
     * of the passed snippet name.
     *
     * @param $name
     * @return string
     */
    private function getSnippetName($name)
    {
        $name = substr($name, 2);
        return substr($name, 0, strlen($name) - 2);
    }

}