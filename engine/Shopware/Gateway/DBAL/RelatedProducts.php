<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Struct;

class RelatedProducts
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

    public function getList(array $products)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }

        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select(array('product.id'))
            ->addSelect('relatedVariant.ordernumber as number');

        $query->from('s_articles_relationships', 'relation');

        $query->innerJoin(
            'relation',
            's_articles',
            'product',
            'product.id = relation.articleID'
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

        $query->where('product.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_GROUP);

        $related = array();
        foreach ($data as $productId => $row) {
            $related[$productId] = array_column($row, 'number');
        }

        return $related;
    }
}