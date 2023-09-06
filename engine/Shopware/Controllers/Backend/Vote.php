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

use Doctrine\DBAL\Connection;
use Shopware\Models\Article\Vote;

/**
 * @extends Shopware_Controllers_Backend_Application<Vote>
 */
class Shopware_Controllers_Backend_Vote extends Shopware_Controllers_Backend_Application
{
    protected $model = Vote::class;

    protected $alias = 'vote';

    public function save($data)
    {
        if (!empty($data['answer']) && empty($data['answer_date'])) {
            $data['answerDate'] = new DateTime();
        }
        if (empty($data['shopId'])) {
            $data['shop'] = null;
        }

        return parent::save($data);
    }

    protected function getList($offset, $limit, $sort = [], $filter = [], array $wholeParams = [])
    {
        $list = parent::getList($offset, $limit, $sort, $filter, $wholeParams);

        $shopIds = array_column($list['data'], 'shopId');
        $shopIds = array_keys(array_flip(array_filter($shopIds)));

        if (empty($shopIds)) {
            return $list;
        }

        // assign shops over additional query to improve performance
        $shops = $this->getShops($shopIds);

        return $this->assignShops($list, $shops);
    }

    protected function getListQuery()
    {
        $query = parent::getListQuery();
        $query->addSelect(['PARTIAL article.{id, name}']);
        $query->leftJoin('vote.article', 'article');

        return $query;
    }

    protected function getDetailQuery($id)
    {
        $query = parent::getDetailQuery($id);
        $query->addSelect(['PARTIAL article.{id, name}']);
        $query->addSelect('shop');
        $query->leftJoin('vote.article', 'article');
        $query->leftJoin('vote.shop', 'shop');

        return $query;
    }

    protected function getSearchAssociationQuery($association, $model, $search)
    {
        $query = parent::getSearchAssociationQuery($association, $model, $search);

        if ($association == 'article') {
            $query->innerJoin('article.votes', 'votes');
            $query->groupBy('article.id');
        }

        return $query;
    }

    /**
     * @param array{success: true, data: array<array<string, mixed>>, total: int} $list
     * @param array<int, array<string, mixed>>                                    $shops indexed by id
     *
     * @return array{success: true, data: array<array<string, mixed>>, total: int}
     */
    protected function assignShops($list, $shops)
    {
        foreach ($list['data'] as &$row) {
            $id = $row['shopId'];
            $row['shop'] = [];

            if (isset($shops[$id])) {
                $row['shop'] = $shops[$id];
            }
        }

        return $list;
    }

    /**
     * @param array<int, int|string> $shopIds
     *
     * @return array<int, array<string, mixed>> indexed by id
     */
    private function getShops(array $shopIds): array
    {
        if (empty($shopIds)) {
            return [];
        }

        $query = $this->container->get(Connection::class)->createQueryBuilder();
        $query->select('*');
        $query->from('s_core_shops', 'shops');
        $query->where('shops.id IN (:ids)');
        $query->setParameter(':ids', $shopIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);
    }
}
