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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Gateway;
use Shopware\Bundle\StoreFrontBundle\Struct;

class VoteAverageGateway implements Gateway\VoteAverageGatewayInterface
{
    /**
     * @var Hydrator\VoteHydrator
     */
    private $voteHydrator;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    public function __construct(
        Connection $connection,
        Hydrator\VoteHydrator $voteHydrator,
        \Shopware_Components_Config $config
    ) {
        $this->connection = $connection;
        $this->voteHydrator = $voteHydrator;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function get(Struct\BaseProduct $product, Struct\ShopContextInterface $context)
    {
        $votes = $this->getList([$product], $context);

        return array_shift($votes);
    }

    /**
     * {@inheritdoc}
     */
    public function getList($products, Struct\ShopContextInterface $context)
    {
        $ids = [];
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }
        $ids = array_unique($ids);

        $query = $this->connection->createQueryBuilder();

        $query->select([
            'articleID',
            'COUNT(id) as total',
            'points',
        ]);

        $query->from('s_articles_vote', 'vote')
            ->where('vote.articleID IN (:products)')
            ->andWhere('vote.active = 1')
            ->groupBy('vote.articleID')
            ->addGroupBy('vote.points')
            ->orderBy('vote.articleID', 'ASC')
            ->setParameter(':products', $ids, Connection::PARAM_INT_ARRAY);

        if ($this->config->get('displayOnlySubShopVotes')) {
            $query->andWhere('(vote.shop_id = :shopId OR vote.shop_id IS NULL)');
            $query->setParameter(':shopId', $context->getShop()->getId());
        }

        /** @var \Doctrine\DBAL\Driver\ResultStatement $statement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_GROUP);

        $result = [];
        foreach ($products as $product) {
            if (!isset($data[$product->getId()])) {
                continue;
            }

            $key = $product->getNumber();

            $votes = $data[$product->getId()];

            $result[$key] = $this->voteHydrator->hydrateAverage($votes);
        }

        return $result;
    }
}
