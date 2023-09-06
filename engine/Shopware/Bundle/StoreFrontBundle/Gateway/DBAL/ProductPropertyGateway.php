<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\PropertyHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\ProductPropertyGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Property\Set;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductPropertyGateway implements ProductPropertyGatewayInterface
{
    private PropertyHydrator $propertyHydrator;

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

    private Connection $connection;

    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        PropertyHydrator $propertyHydrator
    ) {
        $this->propertyHydrator = $propertyHydrator;
        $this->connection = $connection;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function get(BaseProduct $product, ShopContextInterface $context)
    {
        $properties = $this->getList([$product], $context);

        return array_shift($properties);
    }

    /**
     * {@inheritdoc}
     */
    public function getList($products, ShopContextInterface $context)
    {
        $ids = [];
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }
        $ids = array_unique($ids);

        $query = $this->connection->createQueryBuilder();

        $query
            ->addSelect('products.id as productId')
            ->addSelect('relations.position as __relations_position')
            ->addSelect($this->fieldHelper->getPropertySetFields())
            ->addSelect($this->fieldHelper->getPropertyGroupFields())
            ->addSelect($this->fieldHelper->getPropertyOptionFields())
            ->addSelect($this->fieldHelper->getMediaFields())
        ;

        $query->from('s_filter_articles', 'filterArticles')
            ->innerJoin('filterArticles', 's_articles', 'products', 'products.id = filterArticles.articleID')
            ->innerJoin('filterArticles', 's_filter_values', 'propertyOption', 'propertyOption.id = filterArticles.valueID')
            ->innerJoin('products', 's_filter', 'propertySet', 'propertySet.id = products.filtergroupID')
            ->innerJoin('propertySet', 's_filter_relations', 'relations', 'relations.groupID = propertySet.id')
            ->innerJoin('propertyOption', 's_filter_options', 'propertyGroup', 'propertyGroup.id = propertyOption.optionID AND relations.optionID = propertyGroup.id')
            ->leftJoin('propertyOption', 's_media', 'media', 'propertyOption.media_id = media.id')
            ->leftJoin('propertySet', 's_filter_attributes', 'propertySetAttribute', 'propertySetAttribute.filterID = propertySet.id')
            ->leftJoin('propertyGroup', 's_filter_options_attributes', 'propertyGroupAttribute', 'propertyGroupAttribute.optionID = propertyGroup.id')
            ->leftJoin('propertyOption', 's_filter_values_attributes', 'propertyOptionAttribute', 'propertyOptionAttribute.valueID = propertyOption.id')
            ->leftJoin('media', 's_media_attributes', 'mediaAttribute', 'mediaAttribute.mediaID = media.id')
            ->leftJoin('media', 's_media_album_settings', 'mediaSettings', 'mediaSettings.albumID = media.albumID')
            ->where('products.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY)
            ->orderBy('filterArticles.articleID');

        $this->fieldHelper->addPropertySetTranslation($query, $context);
        $this->fieldHelper->addPropertyGroupTranslation($query, $context);
        $this->fieldHelper->addPropertyOptionTranslation($query, $context);
        $this->fieldHelper->addMediaTranslation($query, $context);

        $data = $query->execute()->fetchAll(PDO::FETCH_GROUP);

        $properties = [];
        foreach ($data as $productId => $values) {
            $properties[$productId] = $this->propertyHydrator->hydrateValues($values);
        }

        $result = [];
        foreach ($products as $product) {
            if (!isset($properties[$product->getId()])) {
                continue;
            }
            $sets = $properties[$product->getId()];
            $set = array_shift($sets);
            if (!$set instanceof Set) {
                continue;
            }

            $result[$product->getNumber()] = $set;
        }

        return $result;
    }
}
