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
use Shopware\Bundle\StoreFrontBundle\Gateway\ListProductQueryHelperInterface;
use Shopware\Bundle\StoreFrontBundle\Struct;

class ListProductQueryHelper implements ListProductQueryHelperInterface
{
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
    protected $fieldHelper;

    /**
     * @var \Shopware_Components_Config
     */
    protected $config;

    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(FieldHelper $fieldHelper, \Shopware_Components_Config $config, Connection $connection)
    {
        $this->fieldHelper = $fieldHelper;
        $this->config = $config;
        $this->connection = $connection;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getQuery(array $numbers, Struct\ShopContextInterface $context)
    {
        $esdQuery = $this->getEsdQuery();
        $customerGroupQuery = $this->getCustomerGroupQuery();
        $availableVariantQuery = $this->getHasAvailableVariantQuery();
        $fallbackPriceQuery = $this->getPriceCountQuery(':fallback');
        $query = $this->connection->createQueryBuilder();
        $query->select($this->fieldHelper->getArticleFields())
            ->addSelect($this->fieldHelper->getTopSellerFields())
            ->addSelect($this->fieldHelper->getVariantFields())
            ->addSelect($this->fieldHelper->getUnitFields())
            ->addSelect($this->fieldHelper->getTaxFields())
            ->addSelect($this->fieldHelper->getPriceGroupFields())
            ->addSelect($this->fieldHelper->getManufacturerFields())
            ->addSelect($this->fieldHelper->getEsdFields())
            ->addSelect('(' . $esdQuery->getSQL() . ') as __product_has_esd')
            ->addSelect('(' . $customerGroupQuery->getSQL() . ') as __product_blocked_customer_groups')
            ->addSelect('(' . $availableVariantQuery->getSQL() . ') as __product_has_available_variants')
            ->addSelect('(' . $fallbackPriceQuery->getSQL() . ') as __product_fallback_price_count')
            ->addSelect('manufacturerMedia.id as __manufacturer_img_id')
        ;
        $query->setParameter(':fallback', $context->getFallbackCustomerGroup()->getKey());
        if ($context->getCurrentCustomerGroup()->getId() !== $context->getFallbackCustomerGroup()->getId()) {
            $customerPriceQuery = $this->getPriceCountQuery(':current');
            $query->addSelect('(' . $customerPriceQuery->getSQL() . ') as __product_custom_price_count');
            $query->setParameter(':current', $context->getCurrentCustomerGroup()->getKey());
        }
        $query->from('s_articles_details', 'variant')
            ->innerJoin('variant', 's_articles', 'product', 'product.id = variant.articleID')
            ->innerJoin('product', 's_core_tax', 'tax', 'tax.id = product.taxID')
            ->leftJoin('variant', 's_core_units', 'unit', 'unit.id = variant.unitID')
            ->leftJoin('product', 's_articles_supplier', 'manufacturer', 'manufacturer.id = product.supplierID')
            ->leftJoin('product', 's_core_pricegroups', 'priceGroup', 'priceGroup.id = product.pricegroupID')
            ->leftJoin('variant', 's_articles_attributes', 'productAttribute', 'productAttribute.articledetailsID = variant.id')
            ->leftJoin('product', 's_articles_supplier_attributes', 'manufacturerAttribute', 'manufacturerAttribute.supplierID = product.supplierID')
            ->leftJoin('product', 's_articles_top_seller_ro', 'topSeller', 'topSeller.article_id = product.id')
            ->leftJoin('variant', 's_articles_esd', 'esd', 'esd.articledetailsID = variant.id')
            ->leftJoin('esd', 's_articles_esd_attributes', 'esdAttribute', 'esdAttribute.esdID = esd.id')
            ->leftJoin('manufacturer', 's_media', 'manufacturerMedia', 'manufacturerMedia.path = manufacturer.img')
            ->where('variant.ordernumber IN (:numbers)')
            ->andWhere('variant.active = 1')
            ->andWhere('product.active = 1')
            ->setParameter(':numbers', $numbers, Connection::PARAM_STR_ARRAY);
        if ($this->config->get('hideNoInstock')) {
            $query->andHaving('__product_has_available_variants >= 1');
        }
        $this->fieldHelper->addProductTranslation($query, $context);
        $this->fieldHelper->addVariantTranslation($query, $context);
        $this->fieldHelper->addManufacturerTranslation($query, $context);
        $this->fieldHelper->addUnitTranslation($query, $context);
        $this->fieldHelper->addEsdTranslation($query, $context);

        return $query;
    }

    /**
     * @param string $key
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getPriceCountQuery($key)
    {
        $query = $this->connection->createQueryBuilder();
        if ($this->config->get('calculateCheapestPriceWithMinPurchase')) {
            $query->addSelect('COUNT(DISTINCT ROUND(prices.price * priceVariant.minpurchase, 2)) as priceCount');
        } else {
            $query->addSelect('COUNT(DISTINCT ROUND(prices.price, 2)) as priceCount');
        }
        $query->from('s_articles_prices', 'prices')
            ->innerJoin(
                'prices',
                's_articles_details',
                'priceVariant',
                'priceVariant.id = prices.articledetailsID and priceVariant.active = 1'
            )
            ->andWhere('prices.from = 1')
            ->andWhere('prices.pricegroup = ' . $key)
            ->andWhere('prices.articleID = product.id');
        if ($this->config->get('hideNoInStock')) {
            $query->andWhere('(priceVariant.laststock * priceVariant.instock) >= (priceVariant.laststock * priceVariant.minpurchase)');
        }

        return $query;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getEsdQuery()
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('1')
            ->from('s_articles_esd', 'variantEsd')
            ->where('variantEsd.articleID = product.id')
            ->setMaxResults(1);

        return $query;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getCustomerGroupQuery()
    {
        $query = $this->connection->createQueryBuilder();
        $query->select("GROUP_CONCAT(customerGroups.customergroupId SEPARATOR '|')")
            ->from('s_articles_avoid_customergroups', 'customerGroups')
            ->where('customerGroups.articleID = product.id');

        return $query;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getHasAvailableVariantQuery()
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('COUNT(availableVariant.id)')
            ->from('s_articles_details', 'availableVariant')
            ->where('availableVariant.articleID = product.id')
            ->andWhere('availableVariant.active = 1')
            ->andWhere('(availableVariant.laststock * availableVariant.instock) >= (availableVariant.laststock * availableVariant.minpurchase)');

        return $query;
    }
}
