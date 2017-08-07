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
use Shopware\Context\TranslationContext;
use Shopware\Framework\Struct\FieldHelper;

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
            ->addSelect('GROUP_CONCAT(customerGroups.customergroupID) as __category_customer_groups')
        ;

        $query->from('s_categories', 'category')
            ->leftJoin('category', 's_core_shops', 'shop', 'shop.category_id = category.id')
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

        $collection = new CategoryCollection();
        foreach ($data as $row) {
            $collection->add(
                $this->hydrator->hydrate($row)
            );
        }

        return $collection;
    }
}
