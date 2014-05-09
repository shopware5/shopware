<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Struct;

class RelatedProducts extends Gateway
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
            'relatedVariant.ordernumber as number'
        ));

        $query->from('s_articles_relationships', 'relation');

        $query->innerJoin(
            'relation',
            's_articles',
            'mainArticle',
            'mainArticle.id = relation.articleID'
        );

        $query->innerJoin(
            'mainArticle',
            's_articles_details',
            'mainVariant',
            'mainVariant.id = mainArticle.main_detail_id'
        );

        $query->innerJoin(
            'relation',
            's_articles',
            'relatedArticles',
            'relatedArticles.id = relation.relatedArticle'
        );

        $query->innerJoin(
            'relatedArticles',
            's_articles_details',
            'relatedVariant',
            'relatedVariant.id = relatedArticles.main_detail_id'
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