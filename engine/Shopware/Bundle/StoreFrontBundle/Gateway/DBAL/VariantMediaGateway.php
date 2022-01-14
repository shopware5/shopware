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
use Shopware\Bundle\StoreFrontBundle\Gateway\VariantMediaGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class VariantMediaGateway implements VariantMediaGatewayInterface
{
    private Connection $connection;

    private FieldHelper $fieldHelper;

    /**
     * @var Hydrator\MediaHydrator
     */
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
            $ids[] = $product->getVariantId();
        }
        $ids = array_unique($ids);

        $query = $this->getQuery($context);

        $query->andWhere('childImage.article_detail_id IN (:products)')
            ->orderBy('image.main')
            ->addOrderBy('image.position')
            ->setParameter(':products', $ids, Connection::PARAM_INT_ARRAY);

        $data = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($data as $row) {
            $productNumber = (string) $row['number'];
            $imageId = (int) $row['__image_id'];

            $result[$productNumber][$imageId] = $this->hydrator->hydrateProductImage($row);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getCovers($products, ShopContextInterface $context)
    {
        $ids = [];
        foreach ($products as $product) {
            $ids[] = $product->getVariantId();
        }
        $ids = array_unique($ids);

        $query = $this->getQuery($context);

        $query->andWhere('childImage.article_detail_id IN (:products)')
            ->orderBy('image.main')
            ->addOrderBy('image.position')
            ->setParameter(':products', $ids, Connection::PARAM_INT_ARRAY);

        $data = $query->execute()->fetchAll(PDO::FETCH_GROUP);

        $result = [];
        foreach ($data as $number => $row) {
            $cover = array_shift($row);

            $result[$number] = $this->hydrator->hydrateProductImage($cover);
        }

        return $result;
    }

    private function getQuery(ShopContextInterface $context): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();

        $query->addSelect('variant.ordernumber as number')
            ->addSelect($this->fieldHelper->getMediaFields())
            ->addSelect($this->fieldHelper->getImageFields());

        $query->from('s_articles_img', 'image')
            ->innerJoin('image', 's_media', 'media', 'image.media_id = media.id')
            ->innerJoin('media', 's_media_album_settings', 'mediaSettings', 'mediaSettings.albumID = media.albumID')
            ->innerJoin('image', 's_articles_img', 'childImage', 'childImage.parent_id = image.id')
            ->innerJoin('image', 's_articles_details', 'variant', 'variant.id = childImage.article_detail_id')
            ->leftJoin('image', 's_media_attributes', 'mediaAttribute', 'mediaAttribute.mediaID = image.media_id')
            ->leftJoin('image', 's_articles_img_attributes', 'imageAttribute', 'imageAttribute.imageID = image.id');

        $this->fieldHelper->addImageTranslation($query, $context);
        $this->fieldHelper->addMediaTranslation($query, $context);

        return $query;
    }
}
