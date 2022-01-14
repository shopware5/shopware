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
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway\CheapestPriceGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\PriceHydrator;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware_Components_Config;

class CheapestPriceGateway implements CheapestPriceGatewayInterface
{
    private PriceHydrator $priceHydrator;

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

    private Shopware_Components_Config $config;

    private Connection $connection;

    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        PriceHydrator $priceHydrator,
        Shopware_Components_Config $config
    ) {
        $this->connection = $connection;
        $this->priceHydrator = $priceHydrator;
        $this->fieldHelper = $fieldHelper;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function get(
        BaseProduct $product,
        ShopContextInterface $context,
        Group $customerGroup
    ) {
        $prices = $this->getList([$product], $context, $customerGroup);

        return array_shift($prices);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        $products,
        ShopContextInterface $context,
        Group $customerGroup
    ) {
        /**
         * Contains the cheapest price logic which product price should be selected.
         */
        $ids = $this->getCheapestPriceIds($products, $customerGroup);

        $query = $this->connection->createQueryBuilder();

        $query->select($this->fieldHelper->getPriceFields())
            ->addSelect($this->fieldHelper->getUnitFields());

        $query->from('s_articles_prices', 'price')
            ->innerJoin('price', 's_articles_details', 'variant', 'variant.id = price.articledetailsID')
            ->innerJoin('variant', 's_articles', 'product', 'product.id = variant.articleID')
            ->leftJoin('variant', 's_core_units', 'unit', 'unit.id = variant.unitID')
            ->leftJoin('price', 's_articles_prices_attributes', 'priceAttribute', 'priceAttribute.priceID = price.id')
            ->andWhere('price.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addUnitTranslation($query, $context);
        $this->fieldHelper->addProductTranslation($query, $context);
        $this->fieldHelper->addVariantTranslation($query, $context);
        $this->fieldHelper->addPriceTranslation($query, $context);

        $data = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

        $prices = [];
        foreach ($data as $row) {
            $product = $row['__price_articleID'];
            $prices[$product] = $this->priceHydrator->hydrateCheapestPrice($row);
        }

        return $prices;
    }

    /**
     * Pre selection of the cheapest prices ids.
     *
     * @param BaseProduct[] $products
     *
     * @return array<int>
     */
    protected function getCheapestPriceIds($products, Group $customerGroup)
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

        /*
         * This part of the query handles the closeout products.
         *
         * The `laststock` column contains "1" if the product is a closeout product.
         * In the case that the product contains the closeout flag,
         * the stock and minpurchase are used as they defined in the database
         *
         * In the case that the product isn't a closeout product,
         * the stock and minpurchase are set to 0
         */
        $subQuery->andWhere(
            '(variant.laststock * variant.instock) >= (variant.laststock * variant.minpurchase)'
        );

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

        return $query->execute()->fetchAll(PDO::FETCH_COLUMN);
    }
}
