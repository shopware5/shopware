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
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Gateway;
use Shopware\Bundle\StoreFrontBundle\Struct;

class CategoryQueryHelper implements Gateway\CategoryQueryHelperInterface
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
    private $fieldHelper;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper
    ) {
        $this->connection = $connection;
        $this->fieldHelper = $fieldHelper;
    }

    public function getQuery(array $ids, Struct\ShopContextInterface $context): QueryBuilder
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
            ->andWhere('category.shops IS NULL OR category.shops LIKE :shopId')
            ->addGroupBy('category.id')
            ->setParameter(':categories', $ids, Connection::PARAM_INT_ARRAY)
            ->setParameter(':shopId', '%|' . $context->getShop()->getId() . '|%');

        $this->fieldHelper->addCategoryTranslation($query, $context);
        $this->fieldHelper->addMediaTranslation($query, $context);
        $this->fieldHelper->addProductStreamTranslation($query, $context);
        $this->fieldHelper->addCategoryMainDataTranslation($query, $context);

        return $query;
    }

    public function getMapping(array $ids): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();

        $query->select(['mapping.articleID', 'GROUP_CONCAT(DISTINCT mapping.categoryID)']);

        $query->from('s_articles_categories_ro', 'mapping')
            ->where('mapping.articleID IN (:ids)')
            ->setParameter(':ids', array_values($ids), Connection::PARAM_INT_ARRAY)
            ->groupBy('mapping.articleID');

        return $query;
    }
}
