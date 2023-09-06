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
use Shopware\Bundle\StoreFrontBundle\Gateway\ConfiguratorOptionsGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\ConfiguratorHydrator;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ConfiguratorOptionsGateway implements ConfiguratorOptionsGatewayInterface
{
    private ConfiguratorHydrator $hydrator;

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
        ConfiguratorHydrator $configuratorHydrator
    ) {
        $this->connection = $connection;
        $this->hydrator = $configuratorHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(array $optionIds, ShopContextInterface $context)
    {
        $query = $this->connection->createQueryBuilder();

        $query->addSelect($this->fieldHelper->getConfiguratorGroupFields());
        $query->addSelect($this->fieldHelper->getConfiguratorOptionFields());
        $query->addSelect($this->fieldHelper->getMediaFields());

        $query->from('s_article_configurator_groups', 'configuratorGroup');
        $query->innerJoin('configuratorGroup', 's_article_configurator_options', 'configuratorOption', 'configuratorOption.group_id = configuratorGroup.id');
        $query->leftJoin('configuratorGroup', 's_article_configurator_groups_attributes', 'configuratorGroupAttribute', 'configuratorGroupAttribute.groupID = configuratorGroup.id');
        $query->leftJoin('configuratorOption', 's_article_configurator_options_attributes', 'configuratorOptionAttribute', 'configuratorOptionAttribute.optionID = configuratorOption.id');

        $query->leftJoin('configuratorOption', 's_media', 'media', 'media.id = configuratorOption.media_id');
        $query->leftJoin('media', 's_media_attributes', 'mediaAttribute', 'mediaAttribute.mediaID = media.id');
        $query->leftJoin('media', 's_media_album_settings', 'mediaSettings', 'mediaSettings.albumID = media.albumID');

        $query->addOrderBy('configuratorGroup.position');
        $query->addOrderBy('configuratorGroup.name');
        $query->addOrderBy('configuratorOption.position');
        $query->addOrderBy('configuratorOption.name');
        $query->groupBy('configuratorOption.id');
        $query->andWhere('configuratorOption.id IN (:ids)');
        $query->setParameter('ids', $optionIds, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addConfiguratorGroupTranslation($query, $context);
        $this->fieldHelper->addConfiguratorOptionTranslation($query, $context);
        $this->fieldHelper->addMediaTranslation($query, $context);

        $data = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

        return $this->hydrator->hydrateGroups($data);
    }
}
