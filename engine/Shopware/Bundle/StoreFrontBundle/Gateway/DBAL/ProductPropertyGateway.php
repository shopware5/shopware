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
use Shopware\Bundle\StoreFrontBundle\Gateway;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Gateway\DBAL
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProductPropertyGateway implements Gateway\ProductPropertyGatewayInterface
{
    /**
     * @var Hydrator\PropertyHydrator
     */
    private $propertyHydrator;

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
     * @param Connection $connection
     * @param FieldHelper $fieldHelper
     * @param Hydrator\PropertyHydrator $propertyHydrator
     */
    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        Hydrator\PropertyHydrator $propertyHydrator
    ) {
        $this->propertyHydrator = $propertyHydrator;
        $this->connection = $connection;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * @inheritdoc
     */
    public function get(Struct\BaseProduct $product, Struct\ShopContextInterface $context)
    {
        $properties = $this->getList([$product], $context);

        return array_shift($properties);
    }

    /**
     * @inheritdoc
     */
    public function getList($products, Struct\ShopContextInterface $context)
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

        $query->from('s_filter_articles', 'filterArticles');

        $query->innerJoin(
            'filterArticles',
            's_articles',
            'products',
            'products.id = filterArticles.articleID'
        );

        $query->innerJoin(
            'filterArticles',
            's_filter_values',
            'propertyOption',
            'propertyOption.id = filterArticles.valueID'
        );

        $query->innerJoin(
            'products',
            's_filter',
            'propertySet',
            'propertySet.id = products.filtergroupID'
        );

        $query->leftJoin(
            'propertySet',
            's_filter_attributes',
            'propertySetAttribute',
            'propertySetAttribute.filterID = propertySet.id'
        );

        $query->innerJoin(
            'propertySet',
            's_filter_relations',
            'relations',
            'relations.groupID = propertySet.id'
        );

        $query->innerJoin(
            'propertyOption',
            's_filter_options',
            'propertyGroup',
            'propertyGroup.id = propertyOption.optionID AND relations.optionID = propertyGroup.id'
        );

        $query->leftJoin(
            'propertyOption',
            's_media',
            'media',
            'propertyOption.media_id = media.id'
        );

        $query->leftJoin(
            'media',
            's_media_attributes',
            'mediaAttribute',
            'mediaAttribute.mediaID = media.id'
        );

        $query->leftJoin(
            'media',
            's_media_album_settings',
            'mediaSettings',
            'mediaSettings.albumID = media.albumID'
        );

        $this->fieldHelper->addAllPropertyTranslations($query, $context);

        $query->where('products.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $query->orderBy('filterArticles.articleID');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();
        $data = $statement->fetchAll(\PDO::FETCH_GROUP);

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
            $result[$product->getNumber()] = array_shift($sets);
        }

        return $result;
    }
}
