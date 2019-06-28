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

/**
 * NOTICE:  When doing changes on this file, please remember to do those changes in the CheapestPriceGateway as well!
 */
class CheapestPriceESGateway extends CheapestPriceGateway
{
    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        Hydrator\PriceHydrator $priceHydrator,
        \Shopware_Components_Config $config
    ) {
        parent::__construct($connection, $fieldHelper, $priceHydrator, $config);
        $this->connection = $connection;
        $this->config = $config;
    }

    /**
     * Pre selection of the cheapest prices ids.
     * This method misses the laststock subquery where-condition as laststock-products would be indexed without prices otherwise
     * what would lead to broken price filters when ES is used
     *
     * @param Struct\BaseProduct[] $products
     *
     * @return array
     */
    protected function getCheapestPriceIds($products, Struct\Customer\Group $customerGroup)
    {
        $ids = [];
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }
        $ids = array_unique($ids);

        $subQuery = $this->connection->createQueryBuilder();

        $subQuery->select('prices.id')
            ->from('s_articles_prices', 'prices');

        /*
         * joins the product variants for the min purchase calculation.
         * The cheapest price is defined by prices.price * variant.minpurchase (the real basket price)
         */
        $subQuery->innerJoin(
            'prices',
            's_articles_details',
            'variant',
            'variant.id = prices.articledetailsID'
        );

        /*
         * Joins the products for the closeout validation.
         * Required to select only product prices which product variant can be added to the basket and purchased
         */
        $subQuery->innerJoin(
            'variant',
            's_articles',
            'product',
            'product.id = variant.articleID'
        );

        $subQuery->leftJoin(
            'product',
            's_core_pricegroups_discounts',
            'priceGroup',
            'priceGroup.groupID = product.pricegroupID
             AND priceGroup.discountstart = 1
             AND priceGroup.customergroupID = :priceGroupCustomerGroup
             AND product.pricegroupActive = 1'
        );

        $graduation = 'prices.from = 1';
        if ($this->config->get('useLastGraduationForCheapestPrice')) {
            $graduation = "IF(priceGroup.id IS NOT NULL, prices.from = 1, prices.to = 'beliebig')";
        }

        $subQuery->where('prices.pricegroup = :customerGroup')
            ->andWhere($graduation)
            ->andWhere('variant.active = 1')
            ->andWhere('prices.articleID = outerPrices.articleID');

        $subQuery->setMaxResults(1);

        if ($this->config->get('calculateCheapestPriceWithMinPurchase')) {
            /*
             * Sorting by the cheapest available price
             */
            $subQuery->orderBy('(prices.price * variant.minpurchase)');
        } else {
            /*
             * Sorting by the cheapest unit price
             */
            $subQuery->orderBy('prices.price');
        }

        /**
         * Creates an outer query which allows to
         * select multiple cheapest product prices.
         */
        $query = $this->connection->createQueryBuilder();
        $query->setParameter(':customerGroup', $customerGroup->getKey());

        $query->select('(' . $subQuery->getSQL() . ') as priceId')
            ->from('s_articles_prices', 'outerPrices')
            ->where('outerPrices.articleID IN (:products)')
            ->setParameter(':products', $ids, Connection::PARAM_INT_ARRAY)
            ->setParameter(':priceGroupCustomerGroup', $customerGroup->getId())
            ->groupBy('outerPrices.articleID')
            ->having('priceId IS NOT NULL');

        $statement = $query->execute();

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }
}
