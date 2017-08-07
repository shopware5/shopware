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

namespace Shopware\Product\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Context\TranslationContext;
use Shopware\Framework\Struct\FieldHelper;
use Shopware\Product\Struct\ProductCollection;
use Shopware\Product\Struct\ProductHydrator;

class ProductReader
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var ProductHydrator
     */
    private $hydrator;

    public function __construct(Connection $connection, FieldHelper $fieldHelper, ProductHydrator $hydrator)
    {
        $this->connection = $connection;
        $this->fieldHelper = $fieldHelper;
        $this->hydrator = $hydrator;
    }

    public function read(array $numbers, TranslationContext $context): ProductCollection
    {
        $query = $this->getQuery($numbers, $context);

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $collection = new ProductCollection();
        foreach ($data as $product) {
            $collection->add(
                $this->hydrator->hydrate($product)
            );
        }

        return $collection;
    }

    private function getQuery(array $numbers, TranslationContext $context): QueryBuilder
    {
        $esdQuery = $this->getEsdQuery();
        $customerGroupQuery = $this->getCustomerGroupQuery();
        $availableVariantQuery = $this->getHasAvailableVariantQuery();

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
//            ->addSelect('(' . $fallbackPriceQuery->getSQL() . ') as __product_fallback_price_count')
        ;
        //todo@next move this to store front list product
        //        $query->setParameter(':fallback', $context->getFallbackCustomerGroup()->getKey());
        //        if ($context->getCurrentCustomerGroup()->getId() !== $context->getFallbackCustomerGroup()->getId()) {
        //            $customerPriceQuery = $this->getPriceCountQuery(':current');
        //            $query->addSelect('(' . $customerPriceQuery->getSQL() . ') as __product_custom_price_count');
        //            $query->setParameter(':current', $context->getCurrentCustomerGroup()->getKey());
        //        }

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
            ->where('variant.ordernumber IN (:numbers)')
            ->andWhere('variant.active = 1')
            ->andWhere('product.active = 1')
            ->setParameter(':numbers', $numbers, Connection::PARAM_STR_ARRAY);

        $this->fieldHelper->addProductTranslation($query, $context);
        $this->fieldHelper->addVariantTranslation($query, $context);
        $this->fieldHelper->addManufacturerTranslation($query, $context);
        $this->fieldHelper->addUnitTranslation($query, $context);
        $this->fieldHelper->addEsdTranslation($query, $context);

        return $query;
    }

    /**
     * @return QueryBuilder
     */
    private function getEsdQuery(): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();

        $query->select('1')
            ->from('s_articles_esd', 'variantEsd')
            ->where('variantEsd.articleID = product.id')
            ->setMaxResults(1);

        return $query;
    }

    /**
     * @return QueryBuilder
     */
    private function getCustomerGroupQuery(): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();

        $query->select("GROUP_CONCAT(customerGroups.customergroupId SEPARATOR '|')")
            ->from('s_articles_avoid_customergroups', 'customerGroups')
            ->where('customerGroups.articleID = product.id');

        return $query;
    }

    /**
     * @return QueryBuilder
     */
    private function getHasAvailableVariantQuery(): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();

        $query->select('COUNT(availableVariant.id)')
            ->from('s_articles_details', 'availableVariant')
            ->where('availableVariant.articleID = product.id')
            ->andWhere('availableVariant.active = 1')
            ->andWhere('availableVariant.instock >= availableVariant.minpurchase');

        return $query;
    }
}
