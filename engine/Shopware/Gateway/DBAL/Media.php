<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Thumbnail\Manager;
use Shopware\Gateway\DBAL\Hydrator as Hydrator;
use Shopware\Struct;

class Media extends Gateway
{
    /**
     * @var \Shopware\Gateway\DBAL\Hydrator\Media
     */
    private $mediaHydrator;

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
     * @param \Shopware\Struct\ListProduct $product
     * @return \Shopware\Struct\Media
     */
    public function getCover(Struct\ListProduct $product)
    {
        $covers = $this->getCovers(array($product->getId()));

        return array_shift($covers);
    }

    /**
     * Returns a list of product preview images, which used
     * as product cover in listings or on the detail page.
     *
     * The preview images has the flag "main = 1" in the database.
     *
     * @param Struct\ListProduct[] $products
     * @return Struct\Media[]
     */
    public function getCovers(array $products)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getVariantId();
        }

        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select($this->getMediaFields())
            ->addSelect($this->getImageFields())
            ->addSelect($this->getSettingFields())
            ->addSelect('variants.ordernumber as number')
            ->addSelect($this->getTableFields('s_articles_img_attributes', 'imageAttribute'))
            ->addSelect($this->getTableFields('s_media_attributes', 'attribute'));

        $query->from('s_articles_img', 'image')
            ->innerJoin('image', 's_articles_details', 'variants', 'variants.articleID = image.articleID')
            ->innerJoin('image', 's_media', 'media', 'image.media_id = media.id')
            ->innerJoin('media', 's_media_album_settings', 'settings', 'settings.albumID = media.albumID')
            ->leftJoin('image', 's_articles_img_attributes', 'imageAttribute', 'imageAttribute.imageID = image.id')
            ->leftJoin('media', 's_media_attributes', 'attribute', 'attribute.mediaID = image.media_id');

        $query->where('variants.id IN (:ids)')
            ->andWhere('image.main = 1')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $covers = array();
        foreach ($data as $cover) {
            $number = $cover['number'];

            $cover['thumbnails'] = $this->getMediaThumbnails($cover);

            $covers[$number] = $this->mediaHydrator->hydrateProductImage($cover);
        }

        return $covers;
    }


    private function getMediaFields()
    {
        return array(
            'media.id',
            'media.albumID',
            'media.name',
            'media.description',
            'media.path',
            'media.type',
            'media.extension',
            'media.file_size',
            'media.userID',
            'media.created'
        );
    }

    private function getImageFields()
    {
        return array(
            'image.id as __image_id',
            'image.articleID as __image_articleID',
            'image.img as __image_img',
            'image.main as __image_main',
            'image.description as __image_description',
            'image.position as __image_position',
            'image.width as __image_width',
            'image.height as __image_height',
            'image.extension as __image_extension',
            'image.parent_id as __image_parent_id',
            'image.media_id as __image_media_id'
        );
    }

    private function getSettingFields()
    {
        return array(
            'settings.id as __settings_id',
            'settings.create_thumbnails as __settings_create_thumbnails',
            'settings.thumbnail_size as __settings_thumbnail_size',
            'settings.icon as __settings_icon'
        );
    }

    /**
     * @param $data
     * @return array
     */
    private function getMediaThumbnails($data)
    {
        $sizes = explode(';', $data['__settings_thumbnail_size']);

        $entity = new \Shopware\Models\Media\Media();
        $entity->fromArray($data);

        return $this->thumbnailManager->getMediaThumbnails(
            $entity,
            $sizes
        );
    }

}