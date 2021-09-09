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
use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Service\CategoryServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Category;
use Shopware\Components\Compatibility\LegacyStructConverter;
use Shopware\Components\Theme\LessDefinition;
use Shopware\Models\Config\Element;

/**
 * Shopware AdvancedMenu Plugin
 */
class Shopware_Plugins_Frontend_AdvancedMenu_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    public function install()
    {
        $this->subscribeEvents();
        $this->createForm();

        return true;
    }

    public function enable()
    {
        return [
            'success' => true,
            'invalidateCache' => ['template', 'theme'],
        ];
    }

    public function disable()
    {
        return [
            'success' => true,
            'invalidateCache' => ['template', 'theme'],
        ];
    }

    public function getInfo()
    {
        return [
            'label' => $this->getLabel(),
        ];
    }

    public function getLabel()
    {
        return 'Erweitertes Menü';
    }

    public function onCollectLessFiles(): ArrayCollection
    {
        $lessDir = __DIR__ . '/Views/frontend/_public/src/less/';

        $less = new LessDefinition(
            [],
            [
                $lessDir . 'advanced-menu.less',
            ]
        );

        return new ArrayCollection([$less]);
    }

    public function onCollectJavascriptFiles(): ArrayCollection
    {
        $jsDir = __DIR__ . '/Views/frontend/_public/src/js/';

        return new ArrayCollection([
            $jsDir . 'jquery.advanced-menu.js',
        ]);
    }

    public function onPostDispatch(Enlight_Controller_ActionEventArgs $args): void
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
     *
     * @return array
     */
    public function getAdvancedMenu($category, $activeCategoryId, $depth = null)
    {
        $context = Shopware()->Container()->get(ContextServiceInterface::class)->getShopContext();

        $cacheKey = sprintf(
            'Shopware_AdvancedMenu_Tree_%s_%s_%s',
            $context->getShop()->getId(),
            $category,
            ($this->Config()->get('includeCustomergroup') ? $context->getCurrentCustomerGroup()->getId() : 'x')
        );

        $eventManager = $this->get('events');
        $cacheKey = $eventManager->filter('Shopware_Plugins_AdvancedMenu_CacheKey', $cacheKey, [
            'shopContext' => $context,
            'config' => $this->Config(),
        ]);

        $cache = Shopware()->Container()->get(Zend_Cache_Core::class);

        if ($this->Config()->get('caching') && $cache->test($cacheKey)) {
            $menu = $cache->load($cacheKey, true);
        } else {
            $ids = $this->getCategoryIdsOfDepth($category, $depth);
            $categories = Shopware()->Container()->get(CategoryServiceInterface::class)->getList($ids, $context);
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

    private function subscribeEvents(): void
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

    private function createForm(): void
    {
        $form = $this->Form();

        $parent = $this->Forms()->findOneBy(['name' => 'Frontend']);
        $form->setParent($parent);

        $form->setElement('checkbox', 'show', [
            'label' => 'Menü anzeigen',
            'value' => 1,
            'scope' => Element::SCOPE_SHOP,
        ]);

        $form->setElement('number', 'hoverDelay', [
            'label' => 'Hover Verzögerung (ms)',
            'value' => 250,
            'scope' => Element::SCOPE_SHOP,
        ]);

        $form->setElement('text', 'levels', [
            'label' => 'Anzahl Ebenen',
            'value' => 3,
            'scope' => Element::SCOPE_SHOP,
        ]);

        $form->setElement(
            'select',
            'columnAmount',
            [
                'label' => 'Breite des Teasers',
                'store' => [
                    [0, '0%'],
                    [1, '25%'],
                    [2, '50%'],
                    [3, '75%'],
                    [4, '100%'],
                ],
                'value' => 2,
                'editable' => false,
                'scope' => Element::SCOPE_SHOP,
            ]
        );

        $form->setElement('boolean', 'caching', [
            'label' => 'Caching aktivieren',
            'value' => 1,
            'scope' => Element::SCOPE_SHOP,
        ]);

        $form->setElement('number', 'cachetime', [
            'label' => 'Cachezeit',
            'value' => 86400,
            'scope' => Element::SCOPE_SHOP,
        ]);

        $form->setElement('boolean', 'includeCustomergroup', [
            'label' => 'Kundengruppen für Cache berücksichtigen:',
            'value' => 1,
            'description' => 'Falls aktiv, wird der Cache des Menüs für jede Kundengruppe separat aufgebaut. Nutzen Sie diese Option, falls Sie Kategorien für gewisse Kundengruppen ausgeschlossen haben.<br>Falls inaktiv, erhalten alle Kundengruppen das gleiche Menü aus dem Cache. Diese Einstellung ist zwar performanter, jedoch funktioniert der Kategorieausschluss nach Kundengruppen dann nicht mehr korrekt.',
            'scope' => Element::SCOPE_SHOP,
        ]);

        $this->translateForm();
    }

    private function translateForm(): void
    {
        $translations = [
            'en_GB' => [
                'show' => ['label' => 'Show menu'],
                'levels' => ['label' => 'Category levels'],
                'caching' => ['label' => 'Enable caching'],
                'cachetime' => ['label' => 'Caching time'],
                'columnAmount' => ['label' => 'Teaser width'],
                'hoverDelay' => ['label' => 'Hover delay (ms)'],
                'includeCustomergroup' => ['label' => 'Consider customer groups for cache', 'description' => 'If active, the menu cache is created separately for each customer group. Use this option if you have excluded categories for certain customer groups. <br>If inactive, all customer groups receive the same menu from the cache. This setting is more performant, but the category exclusion by customer groups will then no longer work correctly.'],
            ],
        ];

        $this->addFormTranslations($translations);
    }

    /**
     * @param array[] $categories
     * @param int[]   $actives
     *
     * @return array[]
     */
    private function setActiveFlags(array $categories, array $actives): array
    {
        foreach ($categories as &$category) {
            $category['flag'] = \in_array($category['id'], $actives);

            if (!empty($category['sub'])) {
                $category['sub'] = $this->setActiveFlags($category['sub'], $actives);
            }
        }

        return $categories;
    }

    /**
     * @throws Exception
     *
     * @return int[]
     */
    private function getCategoryPath(int $categoryId): array
    {
        $query = Shopware()->Container()->get(Connection::class)->createQueryBuilder();

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
     * @throws Exception
     *
     * @return int[]
     */
    private function getCategoryIdsOfDepth(int $parentId, int $depth): array
    {
        $query = Shopware()->Container()->get(Connection::class)->createQueryBuilder();
        $query->select('DISTINCT category.id')
              ->from('s_categories', 'category')
              ->where('category.path LIKE :path')
              ->andWhere('category.active = 1')
              ->andWhere('ROUND(LENGTH(path) - LENGTH(REPLACE (path, "|", "")) - 1) <= :depth')
              ->orderBy('category.position')
              ->setParameter(':depth', $depth)
              ->setParameter(':path', '%|' . $parentId . '|%');

        /** @var PDOStatement $statement */
        $statement = $query->execute();

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getCategoriesOfParent(int $parentId, array $categories): array
    {
        $result = [];

        foreach ($categories as $category) {
            if ($category['parentId'] != $parentId) {
                continue;
            }
            $children = $this->getCategoriesOfParent($category['id'], $categories);
            $category['sub'] = $children;
            $category['activeCategories'] = \count($children);
            $result[] = $category;
        }

        return $result;
    }

    /**
     * @param Category[] $categories
     */
    private function convertCategories(array $categories): array
    {
        $converter = Shopware()->Container()->get(LegacyStructConverter::class);

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
}
