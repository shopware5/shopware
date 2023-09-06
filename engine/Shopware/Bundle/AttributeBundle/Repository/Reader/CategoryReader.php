<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\AttributeBundle\Repository\Reader;

use Doctrine\DBAL\Connection;
use PDO;

class CategoryReader extends GenericReader
{
    public function getList($identifiers)
    {
        $categories = parent::getList($identifiers);
        $parents = $this->getParents($categories);

        foreach ($categories as &$row) {
            $path = array_reverse(array_filter(explode('|', $row['path'])));
            $row['parents'] = $this->getPathParents($parents, $path);
        }

        return $categories;
    }

    /**
     * @param array<string> $parents
     * @param string[]      $path
     *
     * @return array<string>
     */
    private function getPathParents(array $parents, array $path): array
    {
        $categories = [];
        foreach ($path as $id) {
            $categories[] = $parents[$id];
        }

        return $categories;
    }

    /**
     * @param array<array<string, mixed>> $data
     *
     * @return array<int, string>
     */
    private function getParents(array $data): array
    {
        $parents = [];
        foreach ($data as $row) {
            $parents = array_merge($parents, explode('|', $row['path']));
        }
        $parents = array_values(array_unique(array_filter($parents)));

        $query = $this->entityManager->getConnection()->createQueryBuilder();
        $query->select('category.id', 'category.description');
        $query->from('s_categories', 'category');
        $query->where('category.id IN (:ids)');
        $query->setParameter(':ids', $parents, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
