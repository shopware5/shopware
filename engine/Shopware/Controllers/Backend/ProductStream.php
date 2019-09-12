<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Condition\CustomerGroupCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContext;
use Shopware\Components\ProductStream\RepositoryInterface;
use Shopware\Models\ProductStream\ProductStream;
use Shopware\Models\Shop\Shop;

class Shopware_Controllers_Backend_ProductStream extends Shopware_Controllers_Backend_Application
{
    protected $model = ProductStream::class;

    protected $alias = 'stream';

    public function copyStreamAttributesAction()
    {
        $sourceStreamId = $this->Request()->getParam('sourceStreamId');
        $targetStreamId = $this->Request()->getParam('targetStreamId');

        $persister = Shopware()->Container()->get('shopware_attribute.data_persister');
        $persister->cloneAttribute(
            's_product_streams_attributes',
            $sourceStreamId,
            $targetStreamId
        );

        $this->View()->assign('success', true);
    }

    public function loadPreviewAction()
    {
        try {
            /** @var RepositoryInterface $streamRepo */
            $streamRepo = $this->get('shopware_product_stream.repository');
            $criteria = new Criteria();

            $sorting = $this->Request()->getParam('sort');

            if ($sorting !== null) {
                /** @var \Shopware\Bundle\SearchBundle\SortingInterface[] $sorting */
                $sorting = $streamRepo->unserialize($sorting);

                foreach ($sorting as $sort) {
                    $criteria->addSorting($sort);
                }
            }

            $conditions = $this->Request()->getParam('conditions');

            if ($conditions !== null) {
                $conditions = json_decode($conditions, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \InvalidArgumentException('Could not decode JSON: ' . json_last_error_msg());
                }

                /** @var \Shopware\Bundle\SearchBundle\ConditionInterface[] $conditions */
                $conditions = $streamRepo->unserialize($conditions);

                foreach ($conditions as $condition) {
                    $criteria->addCondition($condition);
                }
            }

            $criteria->offset($this->Request()->getParam('start', 0));
            $criteria->limit($this->Request()->getParam('limit', 20));

            $context = $this->createContext(
                $this->Request()->getParam('shopId'),
                $this->Request()->getParam('currencyId'),
                $this->Request()->getParam('customerGroupKey')
            );

            $criteria->addBaseCondition(
                new CustomerGroupCondition([$context->getCurrentCustomerGroup()->getId()])
            );

            $category = $context->getShop()->getCategory()->getId();
            $criteria->addBaseCondition(
                new CategoryCondition([$category])
            );

            $result = Shopware()->Container()->get('shopware_search.product_search')
                ->search($criteria, $context);

            $products = array_values($result->getProducts());

            $success = true;
            $error = false;
            $data = $products;
            $total = $result->getTotalCount();
        } catch (\Exception $e) {
            $success = false;
            $error = $e->getMessage();
            $data = [];
            $total = 0;
        }

        $this->View()->assign([
            'success' => $success,
            'data' => $data,
            'total' => $total,
            'error' => $error,
        ]);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function save($data)
    {
        if (isset($data['conditions'])) {
            $data['conditions'] = json_encode($data['conditions']);
        } else {
            $data['conditions'] = null;
        }

        $data['sorting'] = json_encode($data['sorting']);

        return parent::save($data);
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getDetail($id)
    {
        $data = parent::getDetail($id);

        $data['data']['conditions'] = json_decode($data['data']['conditions'], true);
        $data['data']['sorting'] = json_decode($data['data']['sorting'], true);

        return $data;
    }

    public function loadSelectedProductsAction()
    {
        $streamId = $this->Request()->getParam('streamId');
        $query = Shopware()->Container()->get('dbal_connection')->createQueryBuilder();

        $query->select(['product.id', 'variant.ordernumber as number', 'product.name'])
            ->from('s_articles', 'product')
            ->innerJoin('product', 's_product_streams_selection', 'streamProducts', 'streamProducts.article_id = product.id')
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

    public function removeSelectedProductAction()
    {
        $streamId = $this->Request()->getParam('streamId');
        $productId = $this->Request()->getParam('articleId');

        Shopware()->Container()->get('dbal_connection')->executeUpdate(
            'DELETE FROM s_product_streams_selection WHERE stream_id = :streamId AND article_id = :articleId',
            [':streamId' => $streamId, ':articleId' => $productId]
        );

        $this->View()->assign('success', true);
    }

    public function addSelectedProductAction()
    {
        $streamId = $this->Request()->getParam('streamId');
        $productId = $this->Request()->getParam('articleId');

        Shopware()->Container()->get('dbal_connection')->executeUpdate(
            'INSERT IGNORE INTO s_product_streams_selection(stream_id, article_id) VALUES (:streamId, :articleId)',
            [':streamId' => $streamId, ':articleId' => $productId]
        );

        $this->View()->assign('success', true);
    }

    public function getAttributesAction()
    {
        $service = Shopware()->Container()->get('shopware_attribute.crud_service');
        $data = $service->getList('s_articles_attributes');

        $offset = (int) $this->Request()->getParam('start', 0);
        $limit = (int) $this->Request()->getParam('limit', 20);

        $columns = [];
        for ($i = $offset; $i <= $offset + $limit; ++$i) {
            if (!isset($data[$i])) {
                break;
            }
            $struct = $data[$i];
            if (!$struct->displayInBackend()) {
                continue;
            }
            $columns[] = [
                'column' => $struct->getColumnName(),
                'label' => $struct->getLabel() ?: $struct->getColumnName(),
            ];
        }

        $this->View()->assign([
            'success' => true,
            'data' => $columns,
            'total' => count($data),
        ]);
    }

    public function copySelectedProductsAction()
    {
        $sourceStreamId = $this->Request()->getParam('sourceStreamId', false);
        $targetStreamId = $this->Request()->getParam('targetStreamId', false);

        if ($sourceStreamId === $targetStreamId || !$sourceStreamId || !$targetStreamId) {
            return;
        }

        Shopware()->Container()->get('dbal_connection')->executeUpdate(
            'INSERT IGNORE INTO s_product_streams_selection (stream_id, article_id) SELECT :targetStreamId, article_id FROM s_product_streams_selection WHERE stream_id = :sourceStreamId',
            [':targetStreamId' => $targetStreamId, ':sourceStreamId' => $sourceStreamId]
        );

        $this->View()->assign('success', true);
    }

    /**
     * @param int      $shopId
     * @param int|null $currencyId
     * @param int|null $customerGroupKey
     *
     * @throws \InvalidArgumentException if the specified shop couldn't be found
     *
     * @return ProductContext
     */
    private function createContext($shopId, $currencyId = null, $customerGroupKey = null)
    {
        /** @var Shopware\Models\Shop\Repository $repo */
        $repo = Shopware()->Container()->get('models')->getRepository(Shop::class);

        $shop = $repo->getActiveById($shopId);

        if (!$shop) {
            throw new \InvalidArgumentException('Shop not found');
        }

        $shopId = $shop->getId();

        if (!$currencyId) {
            $currencyId = $shop->getCurrency()->getId();
        }

        if (!$customerGroupKey) {
            $customerGroupKey = ContextService::FALLBACK_CUSTOMER_GROUP;
        }

        return Shopware()->Container()->get('shopware_storefront.context_service')
            ->createShopContext($shopId, $currencyId, $customerGroupKey);
    }
}
