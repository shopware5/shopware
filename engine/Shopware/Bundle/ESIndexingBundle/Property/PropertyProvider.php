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

namespace Shopware\Bundle\ESIndexingBundle\Property;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\FieldHelper;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\PropertyHydrator;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use Shopware\Bundle\StoreFrontBundle\Struct\Property\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class PropertyProvider implements PropertyProviderInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var PropertyHydrator
     */
    private $hydrator;

    public function __construct(
        Connection $connection,
        ContextServiceInterface $contextService,
        FieldHelper $fieldHelper,
        PropertyHydrator $hydrator
    ) {
        $this->connection = $connection;
        $this->contextService = $contextService;
        $this->fieldHelper = $fieldHelper;
        $this->hydrator = $hydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function get(Shop $shop, $groupIds)
    {
        $context = $this->contextService->createShopContext(
            $shop->getId(),
            null,
            ContextService::FALLBACK_CUSTOMER_GROUP
        );

        $result = [];
        $query = $this->getQuery($context);

        foreach ($groupIds as $groupId) {
            $query->setParameter(':id', $groupId);
            $data = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
            if (empty($data)) {
                continue;
            }
            $result[$groupId] = $this->hydrateGroup($data);
        }

        return $result;
    }

    /**
     * @param array[] $data
     *
     * @return Group
     */
    private function hydrateGroup($data)
    {
        $group = $this->hydrator->hydrateGroup($data[0]);

        $options = [];
        foreach ($data as $row) {
            $options[] = $this->hydrator->hydrateOption($row);
        }
        $group->setOptions($options);

        return $group;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getQuery(ShopContextInterface $context)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->addSelect($this->fieldHelper->getPropertyGroupFields())
            ->addSelect($this->fieldHelper->getPropertyOptionFields())
            ->addSelect($this->fieldHelper->getMediaFields())
        ;

        $query->from('s_filter_options', 'propertyGroup')
            ->leftJoin('propertyGroup', 's_filter_options_attributes', 'propertyGroupAttribute', 'propertyGroupAttribute.optionID = propertyGroup.id')
            ->innerJoin('propertyGroup', 's_filter_values', 'propertyOption', 'propertyOption.optionID = propertyGroup.id')
            ->leftJoin('propertyOption', 's_filter_values_attributes', 'propertyOptionAttribute', 'propertyOptionAttribute.valueID = propertyOption.id')
            ->leftJoin('propertyOption', 's_media', 'media', 'propertyOption.media_id = media.id')
            ->leftJoin('media', 's_media_attributes', 'mediaAttribute', 'mediaAttribute.mediaID = media.id')
            ->leftJoin('media', 's_media_album_settings', 'mediaSettings', 'mediaSettings.albumID = media.albumID')
        ;

        $this->fieldHelper->addPropertyGroupTranslation($query, $context);
        $this->fieldHelper->addPropertyOptionTranslation($query, $context);
        $this->fieldHelper->addMediaTranslation($query, $context);

        $query->where('propertyGroup.id = :id');

        return $query;
    }
}
