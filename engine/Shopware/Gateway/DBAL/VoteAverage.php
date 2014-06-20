<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator;
use Shopware\Struct;

/**
 * @package Shopware\Gateway\DBAL
 */
class VoteAverage implements \Shopware\Gateway\VoteAverage
{
    /**
     * @var Hydrator\Vote
     */
    private $voteHydrator;

    /**
     * @param \Shopware\Components\Model\ModelManager $entityManager
     * @param Hydrator\Vote $voteHydrator
     */
    function __construct(ModelManager $entityManager, Hydrator\Vote $voteHydrator)
    {
        $this->entityManager = $entityManager;
        $this->voteHydrator = $voteHydrator;
    }

    /**
     * @inheritdoc
     */
    public function get(Struct\ListProduct $product)
    {
        $votes = $this->getList(array($product));

        return array_shift($votes);
    }

    /**
     * @inheritdoc
     */
    public function getList(array $products)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }

        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select(
            array(
                'articleID',
                'COUNT(id) as total',
                'points'
            )
        );

        $query->from('s_articles_vote', 'votes')
            ->where('votes.articleID IN (:products)')
            ->andWhere('votes.active = 1')
            ->groupBy('votes.articleID')
            ->addGroupBy('votes.points')
            ->orderBy('votes.articleID', 'ASC')
            ->addOrderBy('votes.points', 'ASC')
            ->setParameter(':products', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_GROUP);

        $result = array();
        foreach ($products as $product) {
            $key = $product->getNumber();

            $votes = $data[$product->getId()];

            $result[$key] = $this->voteHydrator->hydrateAverage($votes);
        }

        return $result;
    }
}
