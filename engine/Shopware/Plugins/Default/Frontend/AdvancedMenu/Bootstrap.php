<?php

declare(strict_types=1);
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

    /**
     * @return ArrayCollection<int, LessDefinition>
     */
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

    /**
     * @return ArrayCollection<int, string>
     */
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

        if (!$config->get('show')) {
            return;
        }

        $view = $args->getSubject()->View();
        $parent = (int) $this->get('shop')->get('parentID');
        $categoryId = (int) $args->getRequest()->getParam('sCategory', $parent);

        $menu = $this->getAdvancedMenu($parent, $categoryId, (int) $config->get('levels'));

        $view->assign('sAdvancedMenu', $menu);
        $view->assign('columnAmount', $config->get('columnAmount'));

        $view->assign('hoverDelay', $config->get('hoverDelay'));

        $view->addTemplateDir($this->Path() . 'Views');
    }

    /**
     * Returns the complete menu with category path.
     *
     * @param int $category
     * @param int $activeCategoryId
     * @param int $depth
     *
     * @return array<array<string, mixed>>
     */
    public function getAdvancedMenu($category, $activeCategoryId, $depth)
    {
        $context = $this->get(ContextServiceInterface::class)->getShopContext();

        $cacheKey = sprintf(
            'Shopware_AdvancedMenu_Tree_%s_%s_%s',
            $context->getShop()->getId(),
            $category,
            $this->Config()->get('includeCustomergroup') ? $context->getCurrentCustomerGroup()->getId() : 'x'
        );

        $cacheKey = $this->get('events')->filter('Shopware_Plugins_AdvancedMenu_CacheKey', $cacheKey, [
            'shopContext' => $context,
            'config' => $this->Config(),
        ]);

        $cache = $this->get(Zend_Cache_Core::class);

        if ($this->Config()->get('caching') && $cache->test($cacheKey)) {
            $menu = $cache->load($cacheKey, true);
        } else {
            $ids = $this->getCategoryIdsOfDepth($category, (int) $depth);
            $categories = $this->get(CategoryServiceInterface::class)->getList($ids, $context);
            $categoriesArray = $this->convertCategories($categories);
            $categoryTree = $this->getCategoriesOfParent($category, $categoriesArray);
            if ($this->Config()->get('caching')) {
                $cache->save($categoryTree, $cacheKey, ['Shopware_Plugin'], (int) $this->Config()->get('cachetime', 86400));
            }
            $menu = $categoryTree;
        }

        $categoryPath = $this->getCategoryPath($activeCategoryId);

        return $this->setActiveFlags($menu, $categoryPath);
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
     * @param array<array<string, mixed>> $categories
     * @param array<int>                  $actives
     *
     * @return array<array<string, mixed>>
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
     * @return array<int>
     */
    private function getCategoryPath(int $categoryId): array
    {
        $pathString = (string) $this->get(Connection::class)->createQueryBuilder()
            ->select('category.path')
            ->from('s_categories', 'category')
            ->where('category.id = :id')
            ->setParameter(':id', $categoryId)
            ->execute()
            ->fetch(PDO::FETCH_COLUMN);

        $path = array_filter(explode('|', $pathString));
        $path = array_map('\intval', $path);
        $path[] = $categoryId;

        return $path;
    }

    /**
     * @return array<int>
     */
    private function getCategoryIdsOfDepth(int $parentId, int $depth): array
    {
        return $this->get(Connection::class)->createQueryBuilder()
            ->select('DISTINCT category.id')
            ->from('s_categories', 'category')
            ->where('category.path LIKE :path')
            ->andWhere('category.active = 1')
            ->andWhere('ROUND(LENGTH(path) - LENGTH(REPLACE (path, "|", "")) - 1) <= :depth')
            ->orderBy('category.position')
            ->setParameter(':depth', $depth)
            ->setParameter(':path', '%|' . $parentId . '|%')
            ->execute()
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @param array<array<string, mixed>> $categories
     *
     * @return array<array<string, mixed>>
     */
    private function getCategoriesOfParent(int $parentId, array $categories): array
    {
        $result = [];

        foreach ($categories as $category) {
            if ((int) $category['parentId'] !== $parentId) {
                continue;
            }
            $children = $this->getCategoriesOfParent((int) $category['id'], $categories);
            $category['sub'] = $children;
            $category['activeCategories'] = \count($children);
            $result[] = $category;
        }

        return $result;
    }

    /**
     * @param array<Category> $categories
     *
     * @return array<array<string, mixed>>
     */
    private function convertCategories(array $categories): array
    {
        $converter = $this->get(LegacyStructConverter::class);
        $eventManager = $this->get('events');

        return array_map(function (Category $category) use ($converter, $eventManager) {
            $convertedCategory = $converter->convertCategoryStruct($category);

            $convertedCategory['flag'] = false;
            if ($category->getMedia()) {
                $convertedCategory['media']['path'] = $category->getMedia()->getFile();
            }
            if (!empty($category->getExternalLink())) {
                $convertedCategory['link'] = $category->getExternalLink();
            }

            return $eventManager->filter('Shopware_Plugins_AdvancedMenu_ConvertCategory', $convertedCategory, [
                'category' => $category,
            ]);
        }, $categories);
    }
}
