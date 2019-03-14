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

class ProductMediaGateway implements Gateway\ProductMediaGatewayInterface
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
     * @var Hydrator\MediaHydrator
     */
    private $hydrator;

    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        Hydrator\MediaHydrator $hydrator
    ) {
        $this->connection = $connection;
        $this->fieldHelper = $fieldHelper;
        $this->hydrator = $hydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function get(Struct\BaseProduct $product, Struct\ShopContextInterface $context)
    {
        $media = $this->getList([$product], $context);

        return array_shift($media);
    }

    /**
     * {@inheritdoc}
     */
    public function getCover(Struct\BaseProduct $product, Struct\ShopContextInterface $context)
    {
        $covers = $this->getCovers([$product], $context);

        return array_shift($covers);
    }

    /**
     * {@inheritdoc}
     */
    public function getList($products, Struct\ShopContextInterface $context)
    {
        $ids = [];
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }
        $ids = array_unique($ids);

        $query = $this->getQuery($context);

        $query->andWhere('childImage.id IS NULL')
            ->andWhere('image.articleID IN (:products)')
            ->orderBy('image.main')
            ->addOrderBy('image.position')
            ->setParameter(':products', $ids, Connection::PARAM_INT_ARRAY);

        /** @var \Doctrine\DBAL\Driver\ResultStatement $statement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($data as $row) {
            $productId = $row['__image_articleID'];
            $imageId = $row['__image_id'];

            $result[$productId][$imageId] = $this->hydrator->hydrateProductImage($row);
        }

        return $this->assignProductMedia($result, $products);
    }

    /**
     * {@inheritdoc}
     */
    public function getCovers($products, Struct\ShopContextInterface $context)
    {
        $ids = [];
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }
        $ids = array_unique($ids);

        $query = $this->getQuery($context);

        $query->where('image.main = 1')
            ->andWhere('image.articleID IN (:products)')
            ->setParameter(':products', $ids, Connection::PARAM_INT_ARRAY);

        /** @var \Doctrine\DBAL\Driver\ResultStatement $statement */
        $statement = $query->execute();
        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $covers = [];
        foreach ($data as $row) {
            $id = $row['__image_articleID'];

            $covers[$id] = $this->hydrator->hydrateProductImage($row);
        }

        return $this->assignProductMedia($covers, $products);
    }

    /**
     * @param Struct\BaseProduct[] $products
     *
     * @return array
     */
    private function assignProductMedia(array $media, array $products)
    {
        $result = [];
        foreach ($products as $product) {
            $number = $product->getNumber();

            if (!isset($media[$product->getId()])) {
                continue;
            }

            $productMedia = $media[$product->getId()];

            if (!$productMedia) {
                continue;
            }

            $result[$number] = $productMedia;
        }

        return $result;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface $context
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getQuery(Struct\ShopContextInterface $context)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select($this->fieldHelper->getMediaFields())
            ->addSelect($this->fieldHelper->getImageFields());

        $query->from('s_articles_img', 'image')
            ->innerJoin('image', 's_media', 'media', 'image.media_id = media.id')
            ->innerJoin('media', 's_media_album_settings', 'mediaSettings', 'mediaSettings.albumID = media.albumID')
            ->leftJoin('image', 's_media_attributes', 'mediaAttribute', 'mediaAttribute.mediaID = image.media_id')
            ->leftJoin('image', 's_articles_img_attributes', 'imageAttribute', 'imageAttribute.imageID = image.id')
            ->leftJoin('image', 's_articles_img', 'childImage', 'childImage.parent_id = image.id')
            ->andWhere('image.parent_id IS NULL');

        $this->fieldHelper->addImageTranslation($query, $context);
        $this->fieldHelper->addMediaTranslation($query, $context);

        return $query;
    }
}
