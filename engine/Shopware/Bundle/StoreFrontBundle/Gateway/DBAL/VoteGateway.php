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
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Gateway;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Gateway\DBAL
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class VoteGateway implements Gateway\VoteGatewayInterface
{
    /**
     * @var Hydrator\VoteHydrator
     */
    private $voteHydrator;

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
     *
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @param Connection $connection
     * @param FieldHelper $fieldHelper
     * @param Hydrator\VoteHydrator $voteHydrator
     */
    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        Hydrator\VoteHydrator $voteHydrator
    ) {
        $this->voteHydrator = $voteHydrator;
        $this->connection = $connection;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * @inheritdoc
     */
    public function get(Struct\BaseProduct $product, Struct\ShopContextInterface $context)
    {
        $votes = $this->getList([$product], $context);

        return array_shift($votes);
    }

    /**
     * @inheritdoc
     */
    public function getList($products, Struct\ShopContextInterface $context)
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

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

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
