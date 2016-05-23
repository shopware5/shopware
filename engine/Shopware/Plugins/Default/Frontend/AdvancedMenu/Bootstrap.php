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
use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Bundle\StoreFrontBundle\Struct\Category;

/**
 * Shopware AdvancedMenu Plugin
 */
class Shopware_Plugins_Frontend_AdvancedMenu_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Install plugin method
     *
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvents();
        $this->createForm();

        return true;
    }

    /**
     * @return array
     */
    public function enable()
    {
        return [
            'success' => true,
            'invalidateCache' => ['template', 'theme']
        ];
    }

    private function subscribeEvents()
    {
        $this->subscribeEvent(
            'Theme_Compiler_Collect_Plugin_Less',
            'onCollectLessFiles'
        );

        $this->subscribeEvent(
            'Theme_Compiler_Collect_Plugin_Javascript',
            'onCollectJavascriptFiles'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatchSecure_Frontend',
            'onPostDispatch'
        );
    }

    private function createForm()
    {
        $form = $this->Form();

        $parent = $this->Forms()->findOneBy(array('name' => 'Frontend'));
        $form->setParent($parent);

        $form->setElement('checkbox', 'show', array(
            'label' => 'Menü anzeigen',
            'value' => 1,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));

        $form->setElement('number', 'hoverDelay', array(
            'label' => 'Hover Verzögerung (ms)',
            'value' => 250
        ));

        $form->setElement('text', 'levels', array(
            'label' => 'Anzahl Ebenen',
            'value' => 3,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));

        $form->setElement(
            'select',
            'columnAmount',
            array(
                'label' => 'Breite des Teasers',
                'store' => array(
                    array(0, '0%'),
                    array(1, '25%'),
                    array(2, '50%'),
                    array(3, '75%'),
                    array(4, '100%')
                ),
                'value' => 2,
                'editable' => false,
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
            )
        );

        $form->setElement('boolean', 'caching', array(
            'label' => 'Caching aktivieren',
            'value' => 1
        ));

        $form->setElement('number', 'cachetime', array(
            'label' => 'Cachezeit',
            'value' => 86400
        ));

        $this->translateForm();
    }

    private function translateForm()
    {
        $translations = array(
            'en_GB' => array(
                'show' => array('label' => 'Show menu'),
                'levels' => array('label' => 'Category levels'),
                'caching' => array('label' => 'Enable caching'),
                'cachetime' => array('label' => 'Caching time'),
                'columnAmount' => array('label' => 'Teaser width'),
                'hoverDelay' => array('label' => 'Hover delay (ms)')
            )
        );

        $this->addFormTranslations($translations);
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return array(
            'label' => $this->getLabel()
        );
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Erweitertes Menü';
    }

    /**
     * @return ArrayCollection
     */
    public function onCollectLessFiles()
    {
        $lessDir = __DIR__ . '/Views/frontend/_public/src/less/';

        $less = new \Shopware\Components\Theme\LessDefinition(
            array(),
            array(
                $lessDir . 'advanced-menu.less'
            )
        );

        return new ArrayCollection(array($less));
    }

    /**
     * @return ArrayCollection
     */
    public function onCollectJavascriptFiles()
    {
        $jsDir = __DIR__ . '/Views/frontend/_public/src/js/';

        return new ArrayCollection(array(
            $jsDir . 'jquery.advanced-menu.js'
        ));
    }

    /**
     * Event listener method
     *
     * @param Enlight_Controller_ActionEventArgs $args
     */
    public function onPostDispatch(Enlight_Controller_ActionEventArgs $args)
    {
        $config = $this->Config();

        if (!$config->show) {
            return;
        }

        $view = $args->getSubject()->View();
        $parent = Shopware()->Shop()->get('parentID');
        $categoryId = $args->getRequest()->getParam('sCategory', $parent);

        $menu = $this->getAdvancedMenu($parent, $categoryId, (int) $config->levels);

        $view->assign('sAdvancedMenu', $menu);
        $view->assign('columnAmount', $config->columnAmount);

        $view->assign('hoverDelay', $config->hoverDelay);

        $view->addTemplateDir($this->Path() . 'Views');
    }

    /**
     * Returns the complete menu with category path.
     *
     * @param int $category
     * @param int $activeCategoryId
     * @param int $depth
     * @return array
     */
    public function getAdvancedMenu($category, $activeCategoryId, $depth = null)
    {
        $context  = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();
        $cacheKey = 'Shopware_AdvancedMenu_Tree_' . $context->getShop()->getId() . '_' . $category . '_' . Shopware()->System()->sUSERGROUPDATA['id'];
        $cache    = Shopware()->Container()->get('cache');

        if ($this->Config()->get('caching') && $cache->test($cacheKey)) {
            $menu = $cache->load($cacheKey);
        } else {
            $ids = $this->getCategoryIdsOfDepth($category, $depth);
            $categories = Shopware()->Container()->get('shopware_storefront.category_service')->getList($ids, $context);
            $categoriesArray = $this->convertCategories($categories);
            $categoryTree = $this->getCategoriesOfParent($category, $categoriesArray);
            if ($this->Config()->get('caching')) {
                $cache->save($categoryTree, $cacheKey, ['Shopware_Plugin'], (int) $this->Config()->get('cachetime', 86400));
            }
            $menu = $categoryTree;
        }

        $categoryPath = $this->getCategoryPath($activeCategoryId);
        $menu = $this->setActiveFlags($menu, $categoryPath);
        return $menu;
    }

    /**
     * @param array[] $categories
     * @param int[] $actives
     * @return array[]
     */
    private function setActiveFlags($categories, $actives)
    {
        foreach ($categories as &$category) {
            $category['flag'] = in_array($category['id'], $actives);

            if (!empty($category['sub'])) {
                $category['sub'] = $this->setActiveFlags($category['sub'], $actives);
            }
        }
        return $categories;
    }

    /**
     * @param int $categoryId
     * @return int[]
     * @throws Exception
     */
    private function getCategoryPath($categoryId)
    {
        $query = Shopware()->Container()->get('dbal_connection')->createQueryBuilder();

        $query->select('category.path')
            ->from('s_categories', 'category')
            ->where('category.id = :id')
            ->setParameter(':id', $categoryId);

        $path = $query->execute()->fetch(PDO::FETCH_COLUMN);
        $path = explode('|', $path);
        $path = array_filter($path);
        $path[] = $categoryId;

        return $path;
    }

    /**
     * @param int $parentId
     * @param int $depth
     * @return int[]
     * @throws Exception
     */
    private function getCategoryIdsOfDepth($parentId, $depth)
    {
        $query = Shopware()->Container()->get('dbal_connection')->createQueryBuilder();
        $query->select("DISTINCT category.id")
            ->from('s_categories', 'category')
            ->where('category.path LIKE :path')
            ->andWhere('category.active = 1')
            ->andWhere('ROUND(LENGTH(path) - LENGTH(REPLACE (path, "|", "")) - 1) <= :depth')
            ->orderBy('category.position')
            ->setParameter(':depth', $depth)
            ->setParameter(':path', '%|' . $parentId . '|%');

        /**@var $statement PDOStatement*/
        $statement = $query->execute();
        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @param int $parentId
     * @param array $categories
     * @return array
     */
    private function getCategoriesOfParent($parentId, $categories)
    {
        $result = [];

        foreach ($categories as $index => $category) {
            if ($category['parentId'] != $parentId) {
                continue;
            }
            $children = $this->getCategoriesOfParent($category['id'], $categories);
            $category['sub'] = $children;
            $category['activeCategories'] = count($children);
            $result[] = $category;
        }

        return $result;
    }

    /**
     * @param Category[] $categories
     * @return array
     */
    private function convertCategories($categories)
    {
        $converter = Shopware()->Container()->get('legacy_struct_converter');
        return array_map(function (Category $category) use ($converter) {
            $data = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'parentId' => $category->getParentId(),
                'hidetop' => !$category->displayInNavigation(),
                'external' => $category->getExternalLink(),
                'active' => 1,
                'cmsHeadline' => $category->getCmsHeadline(),
                'cmsText' => $category->getCmsText(),
                'position' => $category->getPosition(),
                'link' => 'shopware.php?sViewport=cat&sCategory=' . $category->getId(),
                'media' => null,
                'flag' => false
            ];

            if ($category->isBlog()) {
                $data['link'] = 'shopware.php?sViewport=blog&sCategory=' . $category->getId();
            }

            if ($category->getMedia()) {
                $data['media'] = $converter->convertMediaStruct($category->getMedia());
                $data['media']['path'] = $category->getMedia()->getFile();
            }
            return $data;
        }, $categories);
    }

    /**
     * @param array $categories
     * @param array $path
     * @return array
     */
    private function setActiveCategoriesFlag($categories, $path)
    {
        foreach ($path as $categoryId) {
            $categories[$categoryId]['flag'] = true;
        }

        return $categories;
    }
}
