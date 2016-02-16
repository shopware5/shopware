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
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    private $connection;

    /**
     * @param Connection $connection
     * @param FieldHelper $fieldHelper
     * @param Hydrator\PropertyHydrator $propertyHydrator
     * @param \Shopware_Components_Config $config
     */
    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        Hydrator\PropertyHydrator $propertyHydrator,
        \Shopware_Components_Config $config
    ) {
        $this->propertyHydrator = $propertyHydrator;
        $this->connection = $connection;
        $this->fieldHelper = $fieldHelper;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getList(array $valueIds, Struct\ShopContextInterface $context)
    {
        $query = $this->connection->createQueryBuilder();


        $query
            ->addSelect('relations.position as __relations_position')
            ->addSelect($this->fieldHelper->getPropertySetFields())
            ->addSelect($this->fieldHelper->getPropertyGroupFields())
            ->addSelect($this->fieldHelper->getPropertyOptionFields())
            ->addSelect($this->fieldHelper->getMediaFields())
        ;

        $query->from('s_filter', 'propertySet');

        $query->innerJoin(
            'propertySet',
            's_filter_relations',
            'relations',
            'relations.groupID = propertySet.id'
        );

        $query->leftJoin(
            'propertySet',
            's_filter_attributes',
            'propertySetAttribute',
            'propertySetAttribute.filterID = propertySet.id'
        );

        $query->innerJoin(
            'relations',
            's_filter_options',
            'propertyGroup',
            'relations.optionID = propertyGroup.id
             AND filterable = 1'
        );

        $query->innerJoin(
            'propertyGroup',
            's_filter_values',
            'propertyOption',
            'propertyOption.optionID = propertyGroup.id'
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

        $query->groupBy('propertyOption.id');

        $query->where('propertyOption.id IN (:ids)')
            ->setParameter(':ids', $valueIds, Connection::PARAM_INT_ARRAY);

        $query->orderBy('propertySet.position');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $this->propertyHydrator->hydrateValues($rows);
    }
}
