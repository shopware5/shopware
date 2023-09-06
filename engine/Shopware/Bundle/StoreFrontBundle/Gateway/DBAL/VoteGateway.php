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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\VoteHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\VoteGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware_Components_Config;

class VoteGateway implements VoteGatewayInterface
{
    private VoteHydrator $voteHydrator;

    /**
     * The FieldHelper class is used for the
     * different table column definitions.
     *
     * This class helps to select each time all required
     * table data for the store front.
     *
     * Additionally the field helper reduce the work, to
     * select in a second step the different required
     * attribute tables for a parent table.
     */
    private FieldHelper $fieldHelper;

    private Connection $connection;

    private Shopware_Components_Config $config;

    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        VoteHydrator $voteHydrator,
        Shopware_Components_Config $config
    ) {
        $this->voteHydrator = $voteHydrator;
        $this->connection = $connection;
        $this->fieldHelper = $fieldHelper;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function get(BaseProduct $product, ShopContextInterface $context)
    {
        $votes = $this->getList([$product], $context);

        return array_shift($votes);
    }

    /**
     * {@inheritdoc}
     */
    public function getList($products, ShopContextInterface $context)
    {
        $ids = [];
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }
        $ids = array_unique($ids);

        $query = $this->connection->createQueryBuilder();

        $query->addSelect($this->fieldHelper->getVoteFields());

        $query->from('s_articles_vote', 'vote')
            ->where('vote.articleID IN (:ids)')
            ->andWhere('vote.active = 1')
            ->orderBy('vote.articleID', 'DESC')
            ->addOrderBy('vote.datum', 'DESC')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        if ($this->config->get('displayOnlySubShopVotes')) {
            $query->andWhere('(vote.shop_id = :shopId OR vote.shop_id IS NULL)');
            $query->setParameter(':shopId', $context->getShop()->getId());
        }

        $data = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

        $votes = [];
        foreach ($data as $row) {
            $id = $row['__vote_articleID'];
            $votes[$id][] = $this->voteHydrator->hydrate($row);
        }

        $result = [];
        foreach ($products as $product) {
            $id = $product->getId();

            if (!isset($votes[$id])) {
                continue;
            }

            $number = $product->getNumber();
            $result[$number] = $votes[$id];
        }

        return $result;
    }
}
