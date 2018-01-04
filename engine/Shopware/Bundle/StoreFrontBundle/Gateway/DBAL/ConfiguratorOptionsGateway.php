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

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ConfiguratorOptionsGateway
{
    /**
     * @var Hydrator\ConfiguratorHydrator
     */
    private $hydrator;

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

    /**
     * @var Gateway\MediaGatewayInterface
     */
    private $mediaGateway;

    /**
     * @param Connection                                                      $connection
     * @param FieldHelper                                                     $fieldHelper
     * @param Hydrator\ConfiguratorHydrator                                   $configuratorHydrator
     * @param \Shopware\Bundle\StoreFrontBundle\Gateway\MediaGatewayInterface $mediaGateway
     */
    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        Hydrator\ConfiguratorHydrator $configuratorHydrator,
        Gateway\MediaGatewayInterface $mediaGateway
    ) {
        $this->connection = $connection;
        $this->hydrator = $configuratorHydrator;
        $this->fieldHelper = $fieldHelper;
        $this->mediaGateway = $mediaGateway;
    }

    /**
     * Get groups with options by optionids
     *
     * @param array $optionIds
     *
     * @return Struct\Configurator\Group[]
     */
    public function getOptions(array $optionIds)
    {
        $query = $this->connection->createQueryBuilder();

        //todo@dr media values missing
        $query->addSelect($this->fieldHelper->getConfiguratorGroupFields());
        $query->addSelect($this->fieldHelper->getConfiguratorOptionFields());

        $query->from('s_article_configurator_groups', 'configuratorGroup')
            ->innerJoin('configuratorGroup', 's_article_configurator_options', 'configuratorOption', 'configuratorOption.group_id = configuratorGroup.id')
            ->leftJoin('configuratorGroup', 's_article_configurator_groups_attributes', 'configuratorGroupAttribute', 'configuratorGroupAttribute.groupID = configuratorGroup.id')
            ->leftJoin('configuratorOption', 's_article_configurator_options_attributes', 'configuratorOptionAttribute', 'configuratorOptionAttribute.optionID = configuratorOption.id')
            ->addOrderBy('configuratorGroup.position')
            ->addOrderBy('configuratorGroup.name')
            ->addOrderBy('configuratorOption.position')
            ->addOrderBy('configuratorOption.name')
            ->groupBy('configuratorOption.id')
            ->andWhere('configuratorOption.id IN (:ids)')
            ->setParameter('ids', $optionIds, Connection::PARAM_INT_ARRAY);

        $data = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

        return $this->hydrator->hydrateGroups($data);
    }
}
