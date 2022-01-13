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
use Shopware\Bundle\StoreFrontBundle\Gateway\ProductMediaGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductMediaGateway implements ProductMediaGatewayInterface
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
    public function get(BaseProduct $product, ShopContextInterface $context)
    {
        $media = $this->getList([$product], $context);

        return array_shift($media);
    }

    /**
     * {@inheritdoc}
     */
    public function getCover(BaseProduct $product, ShopContextInterface $context)
    {
        $covers = $this->getCovers([$product], $context);

        return array_shift($covers);
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

        $query = $this->getQuery($context);

        $query->andWhere('childImage.id IS NULL')
            ->andWhere('image.articleID IN (:products)')
            ->orderBy('image.main')
            ->addOrderBy('image.position')
            ->setParameter(':products', $ids, Connection::PARAM_INT_ARRAY);

        $data = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($data as $row) {
            $productId = (int) $row['__image_articleID'];
            $imageId = (int) $row['__image_id'];

            $result[$productId][$imageId] = $this->hydrator->hydrateProductImage($row);
        }

        return $this->assignProductMedia($result, $products);
    }

    /**
     * {@inheritdoc}
     */
    public function getCovers($products, ShopContextInterface $context)
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

        $data = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

        $covers = [];
        foreach ($data as $row) {
            $id = (int) $row['__image_articleID'];

            $covers[$id] = $this->hydrator->hydrateProductImage($row);
        }

        return $this->assignProductMedia($covers, $products);
    }

    /**
     * @template TMedia of mixed
     *
     * @param array<int, TMedia> $media
     * @param BaseProduct[]      $products
     *
     * @return array<string, TMedia>
     */
    private function assignProductMedia(array $media, array $products): array
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

    private function getQuery(ShopContextInterface $context): QueryBuilder
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
