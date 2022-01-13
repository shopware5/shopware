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
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\MediaHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\MediaGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class MediaGateway implements MediaGatewayInterface
{
    private Connection $connection;

    private FieldHelper $fieldHelper;

    private MediaHydrator $hydrator;

    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        MediaHydrator $hydrator
    ) {
        $this->connection = $connection;
        $this->fieldHelper = $fieldHelper;
        $this->hydrator = $hydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id, ShopContextInterface $context)
    {
        $media = $this->getList([$id], $context);

        return array_shift($media);
    }

    /**
     * {@inheritdoc}
     */
    public function getList($ids, ShopContextInterface $context)
    {
        $query = $this->getQuery($context);

        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $data = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($data as $row) {
            $mediaId = (int) $row['__media_id'];
            $result[$mediaId] = $this->hydrator->hydrate($row);
        }

        return $result;
    }

    private function getQuery(ShopContextInterface $context): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();

        $query->select($this->fieldHelper->getMediaFields());

        $query->from('s_media', 'media')
            ->innerJoin('media', 's_media_album_settings', 'mediaSettings', 'mediaSettings.albumID = media.albumID')
            ->leftJoin('media', 's_media_attributes', 'mediaAttribute', 'mediaAttribute.mediaID = media.id')
            ->where('media.id IN (:ids)');

        $this->fieldHelper->addMediaTranslation($query, $context);

        return $query;
    }
}
