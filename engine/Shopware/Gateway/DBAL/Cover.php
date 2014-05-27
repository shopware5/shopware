<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Gateway\DBAL\Hydrator as Hydrator;
use Shopware\Struct;

class Cover extends Media
{
    /**
     * Returns a list of product preview images, which used
     * as product cover in listings or on the detail page.
     *
     * The preview images has the flag "main = 1" in the database.
     *
     * @param Struct\ListProduct[] $products
     * @return Struct\Media[] Indexed by product number
     */
    public function getList(array $products)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getVariantId();
        }

        $query = $this->getProductMediaQuery()
            ->addSelect('variants.ordernumber as number')
            ->innerJoin('image', 's_articles_details', 'variants', 'variants.articleID = image.articleID');

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
}