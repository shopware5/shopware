<?php

namespace Shopware\Gateway\ORM;

use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Thumbnail\Manager;
use Shopware\Hydrator\ORM as Hydrator;
use Shopware\Struct\ProductMini;

class Media
{
    /**
     * @var \Shopware\Hydrator\ORM\Media
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
     * Returns a media struct which contains the definition of the product
     * cover.
     *
     * @param ProductMini $product
     * @return \Shopware\Struct\Media
     */
    public function getProductCover(ProductMini $product)
    {
        $builder = $this->getProductCoverQuery()
            ->andWhere('image.articleId = :productId')
            ->setParameter('productId', $product->getId());

        $data = $builder->getQuery()->getOneOrNullResult(
            AbstractQuery::HYDRATE_ARRAY
        );

        //required to generate the media thumbnail paths
        $sizes = explode(';', $data['media']['album']['settings']['thumbnailSize']);
        $media = new \Shopware\Models\Media\Media();
        $media->fromArray($data['media']);

        $data['media']['thumbnails'] = $this->thumbnailManager->getMediaThumbnails(
            $media,
            $sizes
        );

        return $this->mediaHydrator->hydrateProductImage($data);
    }

    /**
     * Creates a query builder object which selects the cover of products.
     * The query contains only the condition to select product covers.
     * Normally the query will be further restricted to a single product
     * or list of products.
     *
     * @return \Shopware\Components\Model\QueryBuilder
     */
    private function getProductCoverQuery()
    {
        $builder = $this->getProductMediaQuery()
            ->where('image.main = :main')
            ->setParameter('main', true);

        return $builder;
    }

    /**
     * @return \Shopware\Components\Model\QueryBuilder
     */
    private function getProductMediaQuery()
    {
        $builder = $this->entityManager->createQueryBuilder();

        $builder->select(array(
            'image', 'media',
            'imageAttribute', 'mediaAttribute',
            'album', 'settings'
        ));

        $builder->from('Shopware\Models\Article\Image', 'image')
            ->innerJoin('image.media', 'media')
            ->innerJoin('media.album', 'album')
            ->innerJoin('album.settings', 'settings')
            ->leftJoin('image.attribute', 'imageAttribute')
            ->leftJoin('media.attribute', 'mediaAttribute');

        return $builder;
    }
}