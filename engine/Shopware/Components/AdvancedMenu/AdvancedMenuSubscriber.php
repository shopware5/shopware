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

namespace Shopware\Components\AdvancedMenu;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOStatement;
use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs;
use Exception;
use PDO;
use Shopware\Bundle\StoreFrontBundle\Service\Core\CategoryService;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use Shopware\Bundle\StoreFrontBundle\Struct\Category;
use Shopware\Components\Compatibility\LegacyStructConverter;
use Shopware_Components_Config;

class AdvancedMenuSubscriber implements SubscriberInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * @var ContextService
     */
    private $context_service;

    /**
     * @var CategoryService
     */
    private $category_service;

    /**
     * @var LegacyStructConverter
     */
    private $legacy_struct_converter;

    /**
     * AdvancedMenuSubscriber constructor.
     *
     * @param Connection                 $connection
     * @param Shopware_Components_Config $config
     * @param ContextService             $context_service
     * @param CategoryService            $category_service
     * @param LegacyStructConverter      $legacy_struct_converter
     */
    public function __construct(
        Connection $connection,
        Shopware_Components_Config $config,
        ContextService $context_service,
        CategoryService $category_service,
        LegacyStructConverter $legacy_struct_converter
    ) {
        $this->connection = $connection;
        $this->config = $config;
        $this->context_service = $context_service;
        $this->category_service = $category_service;
        $this->legacy_struct_converter = $legacy_struct_converter;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onPostDispatch',
        ];
    }

    /**
     * Event listener method
     *
     * @param Enlight_Controller_ActionEventArgs $args
     */
    public function onPostDispatch(Enlight_Controller_ActionEventArgs $args)
    {
        if (!$this->config->getByNamespace('advancedMenu', 'show')) {
            return;
        }
        $view = $args->getSubject()->View();
        $parent = Shopware()->Shop()->get('parentID');
        $categoryId = $args->getRequest()->getParam('sCategory', $parent);

        $menu = $this->getAdvancedMenu($parent, $categoryId, (int) $this->config->getByNamespace('advancedMenu', 'levels'));

        $view->assign('sAdvancedMenu', $menu);
        $view->assign('columnAmount', $this->config->getByNamespace('advancedMenu', 'columnAmount'));

        $view->assign('hoverDelay', $this->config->getByNamespace('advancedMenu', 'hoverDelay'));

//        $view->addTemplateDir($this->Path() . 'Views');
    }

    /**
     * Returns the complete menu with category path.
     *
     * @param int $category
     * @param int $activeCategoryId
     * @param int $depth
     *
     * @return array
     */
    public function getAdvancedMenu($category, $activeCategoryId, $depth = null)
    {
        $context = $this->context_service->getShopContext();
        $cacheKey = 'Shopware_AdvancedMenu_Tree_' . $context->getShop()->getId() . '_' . $category . '_' . Shopware()->System()->sUSERGROUPDATA['id'];
        $cache = Shopware()->Container()->get('cache');

        if ($this->config->getByNamespace('advancedMenu', 'caching') && $cache->test($cacheKey)) {
            $menu = $cache->load($cacheKey);
        } else {
            $ids = $this->getCategoryIdsOfDepth($category, $depth);
            $categories = $this->category_service->getList($ids, $context);
            $categoriesArray = $this->convertCategories($categories);
            $categoryTree = $this->getCategoriesOfParent($category, $categoriesArray);
            if ($this->config->getByNamespace('advancedMenu', 'caching')) {
                $cache->save($categoryTree, $cacheKey, ['Shopware_Plugin'], (int) $this->config->getByNamespace('advancedMenu', 'cachetime', 86400));
            }
            $menu = $categoryTree;
        }

        $categoryPath = $this->getCategoryPath($activeCategoryId);
        $menu = $this->setActiveFlags($menu, $categoryPath);

        return $menu;
    }

    /**
     * @param int $categoryId
     *
     * @throws Exception
     *
     * @return int[]
     */
    private function getCategoryPath($categoryId)
    {
        /* @var \Doctrine\DBAL\Query\QueryBuilder $query */
        $query = $this->connection->createQueryBuilder();

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
     *
     * @throws Exception
     *
     * @return int[]
     */
    private function getCategoryIdsOfDepth($parentId, $depth)
    {
        /* @var \Doctrine\DBAL\Query\QueryBuilder $query */
        $query = $this->connection->createQueryBuilder();
        $query->select('DISTINCT category.id')
            ->from('s_categories', 'category')
            ->where('category.path LIKE :path')
            ->andWhere('category.active = 1')
            ->andWhere('ROUND(LENGTH(path) - LENGTH(REPLACE (path, "|", "")) - 1) <= :depth')
            ->orderBy('category.position')
            ->setParameter(':depth', $depth)
            ->setParameter(':path', '%|' . $parentId . '|%');

        /** @var $statement PDOStatement */
        $statement = $query->execute();

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @param int   $parentId
     * @param array $categories
     *
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
     *
     * @return array
     */
    private function convertCategories($categories)
    {
        $converter = $this->legacy_struct_converter;

        return array_map(function (Category $category) use ($converter) {
            $data = $converter->convertCategoryStruct($category);

            $data['flag'] = false;
            if ($category->getMedia()) {
                $data['media']['path'] = $category->getMedia()->getFile();
            }
            if (!empty($category->getExternalLink())) {
                $data['link'] = $category->getExternalLink();
            }

            return $data;
        }, $categories);
    }

    /**
     * @param array $categories
     * @param array $path
     *
     * @return array
     */
    private function setActiveCategoriesFlag($categories, $path)
    {
        foreach ($path as $categoryId) {
            $categories[$categoryId]['flag'] = true;
        }

        return $categories;
    }

    /**
     * @param array[] $categories
     * @param int[]   $actives
     *
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
}
