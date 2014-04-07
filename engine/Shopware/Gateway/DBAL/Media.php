<?php

namespace Shopware\Gateway\DBAL;

use Shopware\Components\Model\ModelManager;
use Shopware\Components\Thumbnail\Manager;
use Shopware\Hydrator\DBAL as Hydrator;
use Shopware\Struct\ProductMini;

class Media implements \Shopware\Gateway\Media
{
    /**
     * @var \Shopware\Hydrator\DBAL\Media
     */
    private $mediaHydrator;

    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    private $entityManager;

    /**
     * @var \Shopware\Components\Thumbnail\Manager
     */
    private $thumbnailManager;

    /**
     * @param ModelManager $entityManager
     * @param Hydrator\Media $mediaHydrator
     * @param \Shopware\Components\Thumbnail\Manager $thumbnailManager
     */
    function __construct(
        ModelManager $entityManager,
        Hydrator\Media $mediaHydrator,
        Manager $thumbnailManager
    ) {
        $this->entityManager = $entityManager;
        $this->mediaHydrator = $mediaHydrator;
        $this->thumbnailManager = $thumbnailManager;
    }

    /**
     * Returns the product preview image, which used
     * as product cover in listings or on the detail page.
     *
     * The preview image has the flag "main = 1" in the database.
     *
     * @param \Shopware\Struct\ProductMini $product
     * @return \Shopware\Struct\Media
     */
    public function getProductCover(ProductMini $product)
    {
        $media = $this->getPreviewImage($product);

        $media['imageAttribute'] = $this->getTableRow(
            's_articles_img_attributes',
            $media['image_id'],
            'imageID'
        );

        $media['attribute'] = $this->getTableRow(
            's_media_attributes',
            $media['id'],
            'mediaID'
        );

        $media['thumbnails'] = $this->getMediaThumbnails($media);

        return $this->mediaHydrator->hydrateProductImage($media);
    }

    /**
     * @param $media
     * @return array
     */
    private function getMediaThumbnails($media)
    {
        $settings = $this->getTableRow(
            's_media_album_settings',
            $media['albumID'],
            'albumID'
        );

        $sizes = explode(';', $settings['thumbnail_size']);

        $entity = new \Shopware\Models\Media\Media();
        $entity->fromArray($media);

        return $this->thumbnailManager->getMediaThumbnails(
            $entity,
            $sizes
        );
    }

    /**
     * @param ProductMini $product
     * @return array
     */
    private function getPreviewImage(ProductMini $product)
    {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select(array('media.*', 'images.id as image_id', 'images.main'))
            ->from('s_media', 'media')
            ->innerJoin('media', 's_articles_img', 'images', 'images.media_id = media.id')
            ->where('images.articleID = :productId')
            ->andWhere('images.main = 1')
            ->setParameter(':productId', $product->getId());

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Helper function which selects a whole table by a specify identifier.
     *
     * @param $table
     * @param $id
     * @param string $column
     * @return mixed
     */
    private function getTableRow($table, $id, $column = 'id')
    {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select(array('*'))
            ->from($table, 'entity')
            ->where('entity.' . $column .' = :id')
            ->setParameter(':id', $id);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }
}