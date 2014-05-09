<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Struct;

class SimilarProducts extends Gateway
{
    function __construct(ModelManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param \Shopware\Struct\ListProduct $product
     * @return array Array of order numbers
     */
    public function get(Struct\ListProduct $product)
    {
        $numbers = $this->getList(array($product));

        return array_shift($numbers);
    }

    /**
     * @param Struct\ListProduct[] $products
     * @return array
     */
    public function getList(array $products)
    {
        $ids = array();
        foreach($products as $product) {
            $ids[] = $product->getVariantId();
        }

        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select(array(
            'mainVariant.ordernumber as variant',
            'similarVariant.ordernumber as number'
        ));

        $query->from('s_articles_similar', 'similar');

        $query->innerJoin(
            'similar',
            's_articles',
            'mainArticle',
            'mainArticle.id = similar.articleID'
        );

        $query->innerJoin(
            'mainArticle',
            's_articles_details',
            'mainVariant',
            'mainVariant.id = mainArticle.main_detail_id'
        );

        $query->innerJoin(
            'similar',
            's_articles',
            'similarArticles',
            'similarArticles.id = similar.relatedArticle'
        );

        $query->innerJoin(
            'similarArticles',
            's_articles_details',
            'similarVariant',
            'similarVariant.id = similarArticles.main_detail_id'
        );

        $query->where('mainVariant.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $related = array();
        foreach($data as $row) {
            $variant = $row['variant'];
            $related[$variant][] = $row['number'];
        }

        return $related;
    }
}