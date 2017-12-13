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

namespace Shopware\Bundle\ESIndexingBundle\Product;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\ESIndexingBundle\LastIdQuery;
use Shopware\Bundle\SearchBundle\Facet\VariantFacet;
use Shopware\Bundle\SearchBundleDBAL\VariantHelper;

/**
 * Class ProductQueryFactory
 */
class ProductQueryFactory implements ProductQueryFactoryInterface
{
    /**
     * @var Connection
     */
    private $connection;
    private $variantHelper;

    /**
     * @param Connection    $connection
     * @param VariantHelper $variantHelper
     */
    public function __construct(Connection $connection, VariantHelper $variantHelper)
    {
        $this->connection = $connection;
        $this->variantHelper = $variantHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function createCategoryQuery($categoryId, $limit = null)
    {
        $query = $this->connection->createQueryBuilder()
            ->select(['categories.articleID', 'categories.articleID'])
            ->from('s_articles_categories_ro', 'categories')
            ->andWhere('categories.articleID > :lastId')
            ->andWhere('categories.categoryID = :categoryId')
            ->setParameter(':categoryId', $categoryId, \PDO::PARAM_INT)
            ->setParameter(':lastId', 0, \PDO::PARAM_INT)
            ->orderBy('categories.articleID');

        if ($limit !== null) {
            $query->setMaxResults($limit);
        }

        return new LastIdQuery($query);
    }

    /**
     * {@inheritdoc}
     */
    public function createPriceIdQuery($priceIds, $limit = null)
    {
        $dbal = $this->createQuery($limit)
            ->innerJoin('variant', 's_articles_details', 'subVariant', 'subVariant.articleID = variant.articleID')
            ->innerJoin('subVariant', 's_articles_prices', 'price', 'price.articledetailsID = subVariant.id')
            ->andWhere('price.id IN (:priceIds)')
            ->setParameter(':priceIds', $priceIds, Connection::PARAM_INT_ARRAY);

        return new LastIdQuery($dbal);
    }

    /**
     * {@inheritdoc}
     */
    public function createUnitIdQuery($unitIds, $limit = null)
    {
        $dbal = $this->createQuery($limit)
            ->andWhere('variant.unitID IN (:unitIds)')
            ->setParameter(':unitIds', $unitIds, Connection::PARAM_INT_ARRAY);

        return new LastIdQuery($dbal);
    }

    /**
     * {@inheritdoc}
     */
    public function createVoteIdQuery($voteIds, $limit = null)
    {
        $dbal = $this->createQuery($limit)
            ->innerJoin('product', 's_articles_vote', 'vote', 'vote.articleID = product.id')
            ->andWhere('vote.id IN (:voteIds)')
            ->setParameter(':voteIds', $voteIds, Connection::PARAM_INT_ARRAY);

        return new LastIdQuery($dbal);
    }

    /**
     * {@inheritdoc}
     */
    public function createProductIdQuery($productIds, $limit = null)
    {
        $dbal = $this->createQuery($limit)
            ->andWhere('product.id IN (:productIds)')
            ->setParameter(':productIds', $productIds, Connection::PARAM_INT_ARRAY);

        return new LastIdQuery($dbal);
    }

    /**
     * {@inheritdoc}
     */
    public function createVariantIdQuery($variantIds, $limit = null)
    {
        $dbal = $this->createQuery($limit)
            ->innerJoin('variant', 's_articles_details', 'subVariant', 'subVariant.articleID = variant.articleID')
            ->andWhere('subVariant.id IN (:variantIds)')
            ->setParameter(':variantIds', $variantIds, Connection::PARAM_INT_ARRAY);

        return new LastIdQuery($dbal);
    }

    /**
     * {@inheritdoc}
     */
    public function createTaxQuery($taxIds, $limit = null)
    {
        $dbal = $this->createQuery($limit)
            ->andWhere('product.taxID IN (:taxIds)')
            ->setParameter(':taxIds', $taxIds, Connection::PARAM_INT_ARRAY);

        return new LastIdQuery($dbal);
    }

    /**
     * {@inheritdoc}
     */
    public function createManufacturerQuery($manufacturerIds, $limit = null)
    {
        $dbal = $this->createQuery($limit)
            ->andWhere('product.supplierID IN (:manufacturerIds)')
            ->setParameter(':manufacturerIds', $manufacturerIds, Connection::PARAM_INT_ARRAY);

        return new LastIdQuery($dbal);
    }

    /**
     * {@inheritdoc}
     */
    public function createProductCategoryQuery($categoryIds, $limit = null)
    {
        $dbal = $this->createQuery($limit)
            ->innerJoin('variant', 's_articles_categories_ro', 'shopProducts', 'shopProducts.articleID = variant.articleID')
            ->andWhere('shopProducts.categoryID IN (:categoryIds)')
            ->setParameter(':categoryIds', $categoryIds, Connection::PARAM_INT_ARRAY);

        return new LastIdQuery($dbal);
    }

    /**
     * {@inheritdoc}
     */
    public function createPropertyGroupQuery($groupIds, $limit = null)
    {
        $dbal = $this->createQuery($limit)
            ->innerJoin('variant', 's_filter_articles', 'productProperty', 'variant.articleID = productProperty.articleID')
            ->innerJoin('productProperty', 's_filter_values', 'propertyValue', 'propertyValue.id = productProperty.valueID')
            ->andWhere('propertyValue.optionID IN (:propertyGroupIds)')
            ->setParameter(':propertyGroupIds', $groupIds, Connection::PARAM_INT_ARRAY);

        return new LastIdQuery($dbal);
    }

    /**
     * {@inheritdoc}
     */
    public function createPropertyOptionQuery($optionIds, $limit = null)
    {
        $dbal = $this->createQuery($limit)
            ->innerJoin('variant', 's_filter_articles', 'productProperty', 'variant.articleID = productProperty.articleID')
            ->andWhere('productProperty.valueID IN (:optionIds)')
            ->setParameter(':optionIds', $optionIds, Connection::PARAM_INT_ARRAY);

        return new LastIdQuery($dbal);
    }

    /**
     * @param null|int $limit
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function createQuery($limit = null)
    {
        /**
         * @var VariantFacet
         */
        $variantFacet = $this->variantHelper->getVariantFacet();
        $expandGroups = $variantFacet->getExpandGroupIds();

        $groupResult = null;
        if (!empty($expandGroups)) {
            $groupQuery = $this->connection->createQueryBuilder()
                ->select('variant.ordernumber')
                ->from('s_articles_details', 'variant');

            foreach ($expandGroups as $group) {
                $relation_table_key = 'relation_' . $group;
                $options_table_key = 'options_' . $group;
                $value_group_key = ':group_id_' . $group;

                $groupQuery->innerJoin('variant', 's_article_configurator_option_relations', $relation_table_key, 'variant.id = ' . $relation_table_key . '.article_id')
                    ->innerJoin($relation_table_key, 's_article_configurator_options', $options_table_key, $options_table_key . '.id = ' . $relation_table_key . '.option_id')
                    ->andWhere($options_table_key . '.group_id = ' . $value_group_key)
                    ->setParameter($value_group_key, $group)
                    ->addGroupBy($relation_table_key . '.option_id');
            }

            $groupQuery->andWhere('variant.id > :lastId')
                ->setParameter(':lastId', 0)
                ->addGroupBy('variant.articleID')
                ->orderBy('variant.id');

            $groupResult = $groupQuery->execute()->fetchAll(\PDO::FETCH_COLUMN);
        }

        $mainQuery = $this->connection->createQueryBuilder()
            ->select(['variant.id', 'variant.ordernumber'])
            ->from('s_articles_details', 'variant')
            ->innerJoin('variant', 's_articles', 'product', 'product.id = variant.articleID');

        if (empty($groupResult)) {
            $mainQuery->andWhere('variant.kind = 1');
        } else {
            $mainQuery->andWhere('(variant.kind = 1 or variant.ordernumber IN (:variant_ids))')
                ->setParameter(':variant_ids', $groupResult, Connection::PARAM_STR_ARRAY);
        }

        $mainQuery->andWhere('variant.id > :lastId')
            ->setParameter(':lastId', 0)
            ->orderBy('variant.id');

        if ($limit !== null) {
            $mainQuery->setMaxResults($limit);
        }

        return $mainQuery;
    }
}
