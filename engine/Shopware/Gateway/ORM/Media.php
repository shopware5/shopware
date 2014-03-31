<?php

namespace Shopware\Gateway\ORM;

use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Model\ModelManager;
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
     * @param ModelManager $entityManager
     * @param Hydrator\Media $mediaHydrator
     */
    function __construct(ModelManager $entityManager, Hydrator\Media $mediaHydrator)
    {
        $this->entityManager = $entityManager;
        $this->mediaHydrator = $mediaHydrator;
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
        $builder = $this->entityManager->createQueryBuilder();

        $builder->select(array('image', 'media', 'imageAttribute', 'mediaAttribute'))
            ->from('Shopware\Models\Article\Image', 'image')
            ->innerJoin('image.media', 'media')
            ->leftJoin('image.attribute', 'imageAttribute')
            ->leftJoin('media.attribute', 'mediaAttribute')
            ->where('image.main = :main')
            ->setParameter('main', true);

        return $builder;
    }
}