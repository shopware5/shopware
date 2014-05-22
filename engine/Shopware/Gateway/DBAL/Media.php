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
    protected $mediaHydrator;

    /**
     * @var \Shopware\Components\Thumbnail\Manager
     */
    protected $thumbnailManager;

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
     * @param \Shopware\Struct\ListProduct $product
     * @return \Shopware\Struct\Media
     */
    public function get(Struct\ListProduct $product)
    {
        $covers = $this->getList(array($product->getId()));

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
    public function getVariantsMedia(array $products)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getVariantId();
        }

        $query = $this->getProductMediaQuery();

        $query->resetQueryPart('from');

        $query->addSelect('variant.ordernumber as number')
            ->from('s_articles_img', 'variantImage');

        $query->innerJoin(
            'variantImage',
            's_articles_img',
            'image',
            'variantImage.parent_id = image.id'
        );

        $query->innerJoin(
            'variantImage',
            's_articles_details',
            'variant',
            'variant.id = variantImage.article_detail_id'
        );

        $query->where('variantImage.article_detail_id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY)
            ->orderBy('variantImage.article_detail_id')
            ->addOrderBy('variantImage.main')
            ->addOrderBy('variantImage.position');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $media = array();
        foreach ($data as $row) {
            $key = $row['number'];
            $imageId = $row['id'];

            $row['thumbnails'] = $this->getMediaThumbnails($row);

            $media[$key][$imageId] = $this->mediaHydrator->hydrateProductImage($row);
        }

        return $media;
    }

    /**
     * @param Struct\ListProduct[] $products
     * @return array
     */
    public function getProductsMedia(array $products)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }

        $query = $this->getProductMediaQuery();

        $query->where('image.articleID IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY)
            ->addOrderBy('image.main')
            ->addOrderBy('image.position');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $media = array();
        foreach ($data as $row) {
            $productId = $row['__image_articleID'];
            $imageId = $row['id'];

            $row['thumbnails'] = $this->getMediaThumbnails($row);

            $media[$productId][$imageId] = $this->mediaHydrator->hydrateProductImage($row);
        }

        return $media;
    }

    protected function getProductMediaQuery()
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select($this->getMediaFields())
            ->addSelect($this->getImageFields())
            ->addSelect($this->getSettingFields())
            ->addSelect($this->getTableFields('s_articles_img_attributes', 'imageAttribute'))
            ->addSelect($this->getTableFields('s_media_attributes', 'attribute'));

        $query->from('s_articles_img', 'image')
            ->innerJoin('image', 's_media', 'media', 'image.media_id = media.id')
            ->innerJoin('media', 's_media_album_settings', 'settings', 'settings.albumID = media.albumID')
            ->leftJoin('image', 's_articles_img_attributes', 'imageAttribute', 'imageAttribute.imageID = image.id')
            ->leftJoin('media', 's_media_attributes', 'attribute', 'attribute.mediaID = image.media_id');

        return $query;
    }


    protected function getMediaFields()
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

    protected function getImageFields()
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

    protected function getSettingFields()
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
    protected function getMediaThumbnails($data)
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