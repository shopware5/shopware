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

namespace Shopware\Bundle\AttributeBundle\Repository\Reader;

use Doctrine\DBAL\Connection;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class CustomerStreamReader extends GenericReader
{
    public function getList($identifiers)
    {
        $data = parent::getList($identifiers);

        $counts = $this->getCustomerCounts($identifiers);

        foreach ($data as &$row) {
            $id = (int) $row['id'];
            if (!array_key_exists($id, $counts)) {
                $row['customer_count'] = 0;
            } else {
                $row['customer_count'] = $counts[$id];
            }
        }

        return $data;
    }

    private function getCustomerCounts($ids)
    {
        $query = $this->entityManager->getConnection()->createQueryBuilder();
        $query->select(['stream_id', 'COUNT(customer_id)']);
        $query->from('s_customer_streams_mapping', 'mapping');
        $query->where('mapping.stream_id IN (:ids)');
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);
        $query->groupBy('stream_id');

        return $query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);
    }
}
