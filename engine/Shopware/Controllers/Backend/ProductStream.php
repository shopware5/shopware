<?php

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;

class Shopware_Controllers_Backend_ProductStream extends Shopware_Controllers_Backend_Application
{
    protected $model = 'Shopware\Models\ProductStream\ProductStream';
    protected $alias = 'stream';

    public function loadPreviewAction()
    {
        $conditions = $this->Request()->getParam('conditions');
        $conditions = json_decode($conditions, true);

        $criteria = new Criteria();
        $criteria
            ->offset($this->Request()->getParam('start', 0))
            ->limit($this->Request()->getParam('limit', 20));

        $context = $this->createContext(
            $this->Request()->getParam('shopId', null),
            $this->Request()->getParam('currencyId', null),
            $this->Request()->getParam('customerGroupKey', null)
        );

        $result = Shopware()->Container()->get('shopware_search.product_search')
            ->search($criteria, $context);

        $products = array_values($result->getProducts());

        $this->View()->assign([
            'success' => true,
            'data' => $products,
            'total' => $result->getTotalCount()
        ]);
    }

    public function save($data)
    {
        $data['conditions'] = json_encode($data['conditions']);
        $data['sorting'] = json_encode($data['sorting']);
        return parent::save($data);
    }

    public function getDetail($id)
    {
        $data = parent::getDetail($id);
        $data['data']['conditions'] = json_decode($data['data']['conditions'], true);
        $data['data']['sorting'] = json_decode($data['data']['sorting'], true);
        return $data;
    }

    private function createContext($shopId, $currencyId, $customerGroupKey)
    {
        $repo = Shopware()->Container()->get('models')->getRepository('Shopware\Models\Shop\Shop');
        $shop = $repo->getActiveDefault();

        if (!$shopId) {
            $shopId = $shop->getId();
        }
        if (!$currencyId) {
            $currencyId = $shop->getCurrency()->getId();
        }
        if (!$customerGroupKey) {
            $customerGroupKey = ContextService::FALLBACK_CUSTOMER_GROUP;
        }

        return Shopware()->Container()->get('shopware_storefront.context_service')
            ->createProductContext($shopId, $currencyId, $customerGroupKey);
    }

    public function loadDefinedProductsAction()
    {
        $streamId = $this->Request()->getParam('streamId');
        $query = Shopware()->Container()->get('dbal_connection')->createQueryBuilder();

        $query->select(['product.id', 'variant.ordernumber as number', 'product.name'])
            ->from('s_articles', 'product')
            ->innerJoin('product', 's_product_stream_articles', 'streamProducts', 'streamProducts.article_id = product.id')
            ->innerJoin('product', 's_articles_details', 'variant', 'variant.id = product.main_detail_id')
            ->where('streamProducts.stream_id = :streamId')
            ->setParameter(':streamId', $streamId)
            ->setFirstResult($this->Request()->getParam('start', 0))
            ->setMaxResults($this->Request()->getParam('limit', 25))
        ;

        $products = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

        $query->select('COUNT(product.id) as counter')
            ->setFirstResult(0)->setMaxResults(1);

        $total = $query->execute()->fetch(PDO::FETCH_COLUMN);

        $this->View()->assign(['success' => true, 'data' => $products, 'total' => $total]);
    }

    public function removeDefinedProductAction()
    {
        $streamId = $this->Request()->getParam('streamId');
        $articleId = $this->Request()->getParam('articleId');

        Shopware()->Container()->get('dbal_connection')->executeUpdate(
            "DELETE FROM s_product_stream_articles WHERE stream_id = :streamId AND article_id = :articleId",
            [':streamId' => $streamId, ':articleId' => $articleId]
        );

        $this->View()->assign('success', true);
    }

    public function addDefinedProductAction()
    {
        $streamId = $this->Request()->getParam('streamId');
        $articleId = $this->Request()->getParam('articleId');

        Shopware()->Container()->get('dbal_connection')->executeUpdate(
            "INSERT IGNORE INTO s_product_stream_articles(stream_id, article_id) VALUES (:streamId, :articleId)",
            [':streamId' => $streamId, ':articleId' => $articleId]
        );

        $this->View()->assign('success', true);
    }

    public function getAttributesAction()
    {
        /** @var Connection $connection */
        $connection = $this->get('dbal_connection');
        $schemaManager = $connection->getSchemaManager();
        $tableColumns = $schemaManager->listTableColumns('s_articles_attributes');

        $columns = [];
        foreach ($tableColumns as $column) {
            $columns[] = [
                'column' => $column->getName(),
                'description' => $column->getName()
            ];
        }
        $this->View()->assign(['success' => true, 'data' => $columns]);
    }
}
