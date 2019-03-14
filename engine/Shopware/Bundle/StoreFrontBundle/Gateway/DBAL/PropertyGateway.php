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
use Shopware\Bundle\StoreFrontBundle\Gateway;
use Shopware\Bundle\StoreFrontBundle\Struct;

class PropertyGateway implements Gateway\PropertyGatewayInterface
{
    /**
     * Constant for the alphanumeric sort configuration of the category filters
     */
    const FILTERS_SORT_ALPHANUMERIC = 0;

    /**
     * Constant for the numeric sort configuration of the category filters
     */
    const FILTERS_SORT_NUMERIC = 1;

    /**
     * Constant for the position sort configuration of the category filters
     */
    const FILTERS_SORT_POSITION = 3;

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
     * @var Connection
     */
    private $connection;

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
     * {@inheritdoc}
     */
    public function getList(array $valueIds, Struct\ShopContextInterface $context, array $filterGroupIds = [])
    {
        $query = $this->connection->createQueryBuilder();

        $query
            ->addSelect('relations.position as __relations_position')
            ->addSelect($this->fieldHelper->getPropertySetFields())
            ->addSelect($this->fieldHelper->getPropertyGroupFields())
            ->addSelect($this->fieldHelper->getPropertyOptionFields())
            ->addSelect($this->fieldHelper->getMediaFields())
        ;

        $query->from('s_filter', 'propertySet')
            ->innerJoin('propertySet', 's_filter_relations', 'relations', 'relations.groupID = propertySet.id')
            ->leftJoin('propertySet', 's_filter_attributes', 'propertySetAttribute', 'propertySetAttribute.filterID = propertySet.id')
            ->innerJoin('relations', 's_filter_options', 'propertyGroup', 'relations.optionID = propertyGroup.id AND filterable = 1')
            ->leftJoin('propertyGroup', 's_filter_options_attributes', 'propertyGroupAttribute', 'propertyGroupAttribute.optionID = propertyGroup.id')
            ->innerJoin('propertyGroup', 's_filter_values', 'propertyOption', 'propertyOption.optionID = propertyGroup.id')
            ->leftJoin('propertyOption', 's_filter_values_attributes', 'propertyOptionAttribute', 'propertyOptionAttribute.valueID = propertyOption.id')
            ->leftJoin('propertyOption', 's_media', 'media', 'propertyOption.media_id = media.id')
            ->leftJoin('media', 's_media_attributes', 'mediaAttribute', 'mediaAttribute.mediaID = media.id')
            ->leftJoin('media', 's_media_album_settings', 'mediaSettings', 'mediaSettings.albumID = media.albumID')
            ->where('propertyOption.id IN (:ids)')
            ->groupBy('propertyOption.id')
            ->orderBy('propertySet.position')
            ->setParameter(':ids', $valueIds, Connection::PARAM_INT_ARRAY);

        if ($filterGroupIds) {
            $query->andWhere('propertySet.id IN (:filterSetIds)')
                ->setParameter(':filterSetIds', $filterGroupIds, Connection::PARAM_INT_ARRAY);
        }

        $this->fieldHelper->addMediaTranslation($query, $context);
        $this->fieldHelper->addPropertySetTranslation($query, $context);
        $this->fieldHelper->addPropertyGroupTranslation($query, $context);
        $this->fieldHelper->addPropertyOptionTranslation($query, $context);

        /** @var \Doctrine\DBAL\Driver\ResultStatement $statement */
        $statement = $query->execute();
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $this->propertyHydrator->hydrateValues($rows);
    }
}
