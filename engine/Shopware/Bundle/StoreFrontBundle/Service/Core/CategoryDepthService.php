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

namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Service\CategoryDepthServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Category;

class CategoryDepthService implements CategoryDepthServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function get(Category $category, $depth, array $filterIds = [])
    {
        $depth += count(array_filter($category->getPath()));
        $query = $this->connection->createQueryBuilder();
        $query->select(['category.id', 'category.path'])
            ->from('s_categories', 'category')
            ->andWhere('ROUND(LENGTH(path) - LENGTH(REPLACE (path, "|", "")) - 1) <= :depth')
            ->andWhere('category.active = 1')
            ->setParameter(':depth', $depth)
            ->setParameter(':parent', $category->getId())
        ;

        if (!empty($filterIds)) {
            $query->setParameter(':ids', $filterIds, Connection::PARAM_INT_ARRAY)
                ->andWhere('category.id IN (:ids)');
        }

        $paths = $query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);
        $ids = array_keys($paths);
        $plain = array_values($paths);

        if (count($plain) > 0 && strpos($plain[0], '|') !== false) {
            $rootPath = explode('|', $plain[0]);
            $rootPath = array_filter(array_unique($rootPath));
            $ids = array_merge($ids, $rootPath);

            return $ids;
        }

        return $ids;
    }
}
