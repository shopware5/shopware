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

namespace Shopware\Category\Gateway;

use Doctrine\DBAL\Connection;
use Shopware\Category\Struct\CategoryCollection;
use Shopware\Category\Struct\CategoryHydrator;
use Shopware\Context\Struct\TranslationContext;
use Shopware\Framework\Struct\FieldHelper;
use Shopware\Storefront\ListingPage\ListingPageUrlGenerator;

class CategoryReader
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
     * @var CategoryHydrator
     */
    private $hydrator;

    public function __construct(Connection $connection, FieldHelper $fieldHelper, CategoryHydrator $hydrator)
    {
        $this->connection = $connection;
        $this->fieldHelper = $fieldHelper;
        $this->hydrator = $hydrator;
    }

    public function read(array $ids, TranslationContext $context): CategoryCollection
    {
        $query = $this->connection->createQueryBuilder();

        $query->select($this->fieldHelper->getCategoryFields())
            ->addSelect($this->fieldHelper->getMediaFields())
            ->addSelect($this->fieldHelper->getRelatedProductStreamFields())
            ->addSelect($this->fieldHelper->getSeoUrlFields())
            ->addSelect('GROUP_CONCAT(customerGroups.customer_group_id) as __category_customer_groups')
        ;

        $query->from('category', 'category');
        $query->leftJoin('category', 's_core_shops', 'shop', 'shop.category_id = category.id');
        $query->leftJoin('category', 'category_attribute', 'categoryAttribute', 'categoryAttribute.category_id = category.id');
        $query->leftJoin('category', 'category_avoid_customer_group', 'customerGroups', 'customerGroups.category_id = category.id');
        $query->leftJoin('category', 's_media', 'media', 'media.id = category.media_id');
        $query->leftJoin('media', 's_media_album_settings', 'mediaSettings', 'mediaSettings.albumID = media.albumID');
        $query->leftJoin('media', 's_media_attributes', 'mediaAttribute', 'mediaAttribute.mediaID = media.id');
        $query->leftJoin('category', 's_product_streams', 'stream', 'category.stream_id = stream.id');
        $query->leftJoin('stream', 's_product_streams_attributes', 'productStreamAttribute', 'stream.id = productStreamAttribute.streamId');
        $query->leftJoin('category', 'seo_url', 'seoUrl', 'seoUrl.foreign_key = category.id AND seoUrl.name = :seoUrlName AND is_canonical = 1 AND seoUrl.shop_id = :shopId');
        $query->where('category.id IN (:categories)');
        $query->andWhere('category.active = 1');
        $query->addGroupBy('category.id');
        $query->setParameter('categories', $ids, Connection::PARAM_INT_ARRAY);
        $query->setParameter('shopId', $context->getShopId());
        $query->setParameter(':seoUrlName', ListingPageUrlGenerator::ROUTE_NAME);

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

        $collection = new CategoryCollection();
        foreach ($data as $row) {
            $collection->add(
                $this->hydrator->hydrate($row)
            );
        }

        return $collection;
    }
}
