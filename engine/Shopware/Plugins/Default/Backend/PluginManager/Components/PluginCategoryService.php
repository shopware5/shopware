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

namespace ShopwarePlugins\PluginManager\Components;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOStatement;
use Shopware\Bundle\PluginInstallerBundle\Service\PluginStoreService;
use Shopware\Bundle\PluginInstallerBundle\Struct\CategoryStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\StructHydrator;

class PluginCategoryService
{
    const CATEGORY_HIGHLIGHTS = -1;
    const CATEGORY_NEWCOMER = -2;
    const CATEGORY_RECOMMENDATION = -3;

    /**
     * @var PluginStoreService
     */
    private $pluginService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var StructHydrator
     */
    private $hydrator;

    public function __construct(
        PluginStoreService $pluginService,
        Connection $connection,
        StructHydrator $hydrator
    ) {
        $this->pluginService = $pluginService;
        $this->connection = $connection;
        $this->hydrator = $hydrator;
    }

    /**
     * Loads plugin category data and organizes it in tree format
     * If no categories are found for the original locale, a fallback is used instead
     *
     * @param string $locale
     * @param string $fallbackLocale
     *
     * @return CategoryStruct[]
     */
    public function get($locale, $fallbackLocale)
    {
        $categories = $this->getCategories($locale, $fallbackLocale);

        $firstLevel = $categories[null];

        return $this->buildTree($firstLevel, $categories);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function synchronize()
    {
        $categories = $this->pluginService->getCategories();

        $this->connection->exec('DELETE FROM s_core_plugin_categories');

        $statement = $this->connection->prepare(
            'INSERT INTO s_core_plugin_categories (id, locale, parent_id, name)
             VALUES (:id, :locale, :parent_id, :name)'
        );

        $pseudo = $this->getPseudoCategories();
        foreach ($pseudo as $category) {
            $statement->execute($category);
        }

        foreach ($categories as $category) {
            foreach ($category->getName() as $locale => $name) {
                $statement->execute([
                    ':id' => $category->getId(),
                    ':name' => $name,
                    ':locale' => $locale,
                    ':parent_id' => $category->getParentId(),
                ]);
            }
        }
    }

    /**
     * @return array
     */
    private function getPseudoCategories()
    {
        return [
            [':id' => self::CATEGORY_HIGHLIGHTS,     ':name' => 'Highlights',     ':locale' => 'de_DE', ':parent_id' => null],
            [':id' => self::CATEGORY_HIGHLIGHTS,     ':name' => 'Highlights',     ':locale' => 'en_GB', ':parent_id' => null],
            [':id' => self::CATEGORY_NEWCOMER,       ':name' => 'Neuheiten',      ':locale' => 'de_DE', ':parent_id' => null],
            [':id' => self::CATEGORY_NEWCOMER,       ':name' => 'Newcomer',       ':locale' => 'en_GB', ':parent_id' => null],
            [':id' => self::CATEGORY_RECOMMENDATION, ':name' => 'Empfehlungen',   ':locale' => 'de_DE', ':parent_id' => null],
            [':id' => self::CATEGORY_RECOMMENDATION, ':name' => 'Recommendation', ':locale' => 'en_GB', ':parent_id' => null],
        ];
    }

    /**
     * Creates a nested tree for the provided categories
     *
     * @param CategoryStruct[] $level      level which should be iterate and assigned to the tree
     * @param CategoryStruct[] $categories Grouped by the category parent
     *
     * @return CategoryStruct[]
     */
    private function buildTree($level, $categories)
    {
        foreach ($level as &$category) {
            $id = $category->getId();

            if (isset($categories[$id])) {
                $categories[$id] = $this->buildTree(
                    $categories[$id],
                    $categories
                );

                $category->setChildren($categories[$id]);
            }
        }

        return $level;
    }

    /**
     * Returns all categories, grouped by the parent id
     *
     * @param string $locale
     * @param string $fallbackLocale
     *
     * @return array
     */
    private function getCategories($locale, $fallbackLocale)
    {
        $data = $this->getCategoryDataForLocale($locale);
        if (empty($data)) {
            $data = $this->getCategoryDataForLocale($fallbackLocale);
        }

        $result = [];
        foreach ($data as $key => $grouped) {
            $result[$key] = [];

            foreach ($grouped as $categoryData) {
                $result[$key][] = $this->hydrator->hydrateCategory(
                    $categoryData
                );
            }
        }

        return $result;
    }

    /**
     * Loads category info from the database
     *
     * @param string $locale
     *
     * @return array
     */
    private function getCategoryDataForLocale($locale)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select(
            [
                'categories.parent_id',
                'categories.name',
                'categories.id as categoryId',
                'categories.parent_id as parentId',
            ]
        );

        $query->from('s_core_plugin_categories', 'categories')
            ->where('categories.locale = :locale')
            ->setParameter(':locale', $locale);

        /** @var PDOStatement $statement */
        $statement = $query->execute();

        return $statement->fetchAll(\PDO::FETCH_GROUP);
    }
}
