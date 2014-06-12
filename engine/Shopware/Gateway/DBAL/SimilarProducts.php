<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Struct;

class SimilarProducts
{
    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    private $entityManager;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @param ModelManager $entityManager
     * @param \Shopware_Components_Config $config
     */
    function __construct(
        ModelManager $entityManager,
        \Shopware_Components_Config $config
    ) {
        $this->entityManager = $entityManager;
        $this->config = $config;
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
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }

        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select(
            array(
                'product.id',
                'similarVariant.ordernumber as number'
            )
        );

        $query->from('s_articles_similar', 'similar');

        $query->innerJoin(
            'similar',
            's_articles',
            'product',
            'product.id = similar.articleID'
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

    /**
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     * @return array
     */
    public function getSimilarByCategory(array $products, Struct\Context $context)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }

        $categoryId = 1;
        if ($context->getShop() && $context->getShop()->getCategory()) {
            $categoryId = $context->getShop()->getCategory()->getId();
        }

        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select(array(
            'main.articleID',
            "GROUP_CONCAT(subVariant.ordernumber SEPARATOR '|') as similar"
        ));

        $query->from('s_articles_categories', 'main');

        $query->innerJoin(
            'main',
            's_articles_categories',
            'sub',
            'sub.categoryID = main.categoryID AND sub.articleID != main.articleID'
        );

        $query->innerJoin(
            'sub',
            's_articles_details',
            'subVariant',
            'subVariant.articleID = sub.articleID AND subVariant.kind = 1'
        );

        $query->innerJoin(
            'main',
            's_categories',
            'category',
            'category.id = sub.categoryID AND category.id = main.categoryID'
        );

        $query->where('main.articleID IN (:ids)')
            ->andWhere('category.path LIKE :path');

        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY)
            ->setParameter(':path', '%|'. (int) $categoryId.'|');

        $query->groupBy('main.articleID');

        $statement = $query->execute();
        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $limit = 3;
        if ($this->config->offsetExists('similarLimit') && $this->config->get('similarLimit') > 0) {
            $limit = (int) $this->config->get('similarLimit');
        }

        $result = array();
        foreach ($data as $row) {
            $similar = explode('|', $row['similar']);
            $result[$row['articleID']] = array_slice($similar, 0, $limit);
        }

        return $result;
    }
}
