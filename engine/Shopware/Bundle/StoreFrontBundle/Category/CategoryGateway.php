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

namespace Shopware\Bundle\StoreFrontBundle\Category;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Common\FieldHelper;
use Shopware\Bundle\StoreFrontBundle\Context\TranslationContext;
use Shopware\Bundle\StoreFrontBundle\Product\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CategoryGateway
{
    /**
     * @var CategoryHydrator
     */
    private $categoryHydrator;

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
     * @var \Shopware\Bundle\StoreFrontBundle\Common\FieldHelper
     */
    private $fieldHelper;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection                $connection
     * @param FieldHelper               $fieldHelper
     * @param CategoryHydrator $categoryHydrator
     */
    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        CategoryHydrator $categoryHydrator
    ) {
        $this->connection = $connection;
        $this->categoryHydrator = $categoryHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * @param BaseProduct[]      $products
     * @param TranslationContext $context
     *
     * @return array Indexed by product number, contains all categories of a product
     */
    public function getProductsCategories(array $products, TranslationContext $context)
    {
        $productIds = array_map(function (BaseProduct $product) {
            return $product->getId();
        }, $products);

        $mapping = $this->getMapping($productIds);

        $ids = $this->getMappingIds($mapping);

        $categories = $this->getList($ids, $context);

        $result = [];
        /** @var BaseProduct[] $products */
        foreach ($products as $product) {
            $id = $product->getId();
            if (!isset($mapping[$id])) {
                continue;
            }

            $productCategories = $this->getProductCategories(
                explode(',', $mapping[$id]),
                $categories
            );
            $result[$product->getNumber()] = $productCategories;
        }

        return $result;
    }

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\CategoryGatewayInterface::get()
     *
     * @param int[]                     $ids
     * @param TranslationContext $context
     *
     * @return \Shopware\Bundle\StoreFrontBundle\Category\Category[] Indexed by the category id
     */
    public function getList(array $ids, TranslationContext $context)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select($this->fieldHelper->getCategoryFields())
            ->addSelect($this->fieldHelper->getMediaFields())
            ->addSelect($this->fieldHelper->getRelatedProductStreamFields())
            ->addSelect('GROUP_CONCAT(customerGroups.customergroupID) as __category_customer_groups')
        ;

        $query->from('s_categories', 'category')
            ->leftJoin('category', 's_categories_attributes', 'categoryAttribute', 'categoryAttribute.categoryID = category.id')
            ->leftJoin('category', 's_categories_avoid_customergroups', 'customerGroups', 'customerGroups.categoryID = category.id')
            ->leftJoin('category', 's_media', 'media', 'media.id = category.mediaID')
            ->leftJoin('media', 's_media_album_settings', 'mediaSettings', 'mediaSettings.albumID = media.albumID')
            ->leftJoin('media', 's_media_attributes', 'mediaAttribute', 'mediaAttribute.mediaID = media.id')
            ->leftJoin('category', 's_product_streams', 'stream', 'category.stream_id = stream.id')
            ->leftJoin('stream', 's_product_streams_attributes', 'productStreamAttribute', 'stream.id = productStreamAttribute.streamId')
            ->where('category.id IN (:categories)')
            ->andWhere('category.active = 1')
            ->addGroupBy('category.id')
            ->setParameter(':categories', $ids, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addMediaTranslation($query, $context);
        $this->fieldHelper->addProductStreamTranslation($query, $context);

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        //use php usort instead of running mysql order by to prevent file-sort and temporary table statement
        usort($data, function ($a, $b) {
            if ($a['__category_position'] === $b['__category_position']) {
                return $a['__category_id'] > $b['__category_id'];
            }

            return $a['__category_position'] > $b['__category_position'];
        });

        $categories = [];
        foreach ($data as $row) {
            $id = $row['__category_id'];

            $categories[$id] = $this->categoryHydrator->hydrate($row);
        }

        return $categories;
    }

    /**
     * @param int[] $ids
     *
     * @return string[] indexed by product id
     */
    private function getMapping(array $ids)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select(['mapping.articleID', 'GROUP_CONCAT(DISTINCT mapping.categoryID)']);

        $query->from('s_articles_categories_ro', 'mapping')
            ->where('mapping.articleID IN (:ids)')
            ->setParameter(':ids', array_values($ids), Connection::PARAM_INT_ARRAY)
            ->groupBy('mapping.articleID');

        $mapping = $query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);

        return $mapping;
    }

    /**
     * @param string[] $mapping
     *
     * @return int[]
     */
    private function getMappingIds(array $mapping)
    {
        $ids = [];
        foreach ($mapping as $row) {
            $ids = array_merge($ids, explode(',', $row));
        }
        $ids = array_unique($ids);

        return $ids;
    }

    /**
     * @param int[]             $mapping
     * @param \Shopware\Bundle\StoreFrontBundle\Category\Category[] $categories
     *
     * @return \Shopware\Bundle\StoreFrontBundle\Category\Category[]
     */
    private function getProductCategories(array $mapping, array $categories)
    {
        $productCategories = [];
        foreach ($mapping as $categoryId) {
            if (!isset($categories[$categoryId])) {
                continue;
            }
            $productCategories[] = $categories[$categoryId];
        }

        return $productCategories;
    }
}
