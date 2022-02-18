<?php

declare(strict_types=1);
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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\DataPersister;
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Condition\CustomerGroupCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductSearchInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Price;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Model\Exception\ModelNotFoundException;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\ProductStream\Repository as ProductStreamRepository;
use Shopware\Models\ProductStream\ProductStream;
use Shopware\Models\Shop\Shop;

/**
 * @extends Shopware_Controllers_Backend_Application<ProductStream>
 */
class Shopware_Controllers_Backend_ProductStream extends Shopware_Controllers_Backend_Application
{
    protected $model = ProductStream::class;

    protected $alias = 'stream';

    /**
     * @return void
     */
    public function copyStreamAttributesAction()
    {
        $sourceStreamId = $this->Request()->getParam('sourceStreamId');
        $targetStreamId = $this->Request()->getParam('targetStreamId');

        $persister = Shopware()->Container()->get(DataPersister::class);
        $persister->cloneAttribute(
            's_product_streams_attributes',
            $sourceStreamId,
            $targetStreamId
        );

        $this->View()->assign('success', true);
    }

    /**
     * @return void
     */
    public function loadPreviewAction()
    {
        try {
            $streamRepo = $this->get(ProductStreamRepository::class);
            $criteria = new Criteria();

            $sorting = $this->Request()->getParam('sort');

            if ($sorting !== null) {
                $sorting = $streamRepo->unserialize($sorting);

                foreach ($sorting as $sort) {
                    $criteria->addSorting($sort);
                }
            }

            $conditions = $this->Request()->getParam('conditions');

            if ($conditions !== null) {
                $conditions = json_decode($conditions, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new InvalidArgumentException('Could not decode JSON: ' . json_last_error_msg());
                }

                $conditions = $streamRepo->unserialize($conditions);

                foreach ($conditions as $condition) {
                    $criteria->addCondition($condition);
                }
            }

            $criteria->offset($this->Request()->getParam('start', 0));
            $criteria->limit($this->Request()->getParam('limit', 20));

            $context = $this->createContext(
                (int) $this->Request()->getParam('shopId'),
                (int) $this->Request()->getParam('currencyId'),
                $this->Request()->getParam('customerGroupKey')
            );

            $criteria->addBaseCondition(
                new CustomerGroupCondition([$context->getCurrentCustomerGroup()->getId()])
            );

            $category = $context->getShop()->getCategory()->getId();
            $criteria->addBaseCondition(
                new CategoryCondition([$category])
            );

            $result = Shopware()->Container()->get(ProductSearchInterface::class)
                ->search($criteria, $context);

            $products = array_values($result->getProducts());
            $products = array_map(function (ListProduct $product) {
                $cheapestPrice = $product->getCheapestPrice();
                if (!$cheapestPrice instanceof Price) {
                    return $product;
                }
                $price = $cheapestPrice->getCalculatedPrice();
                $product = json_decode(json_encode($product, JSON_THROW_ON_ERROR), true);
                $product['cheapestPrice'] = $price;

                return $product;
            }, $products);

            $success = true;
            $error = false;
            $data = $products;
            $total = $result->getTotalCount();
        } catch (Exception $e) {
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

    public function getDetail($id)
    {
        $data = parent::getDetail($id);

        $data['data']['conditions'] = isset($data['data']['conditions']) ? json_decode($data['data']['conditions'], true) : null;
        $data['data']['sorting'] = json_decode($data['data']['sorting'], true);

        return $data;
    }

    /**
     * @return void
     */
    public function loadSelectedProductsAction()
    {
        $streamId = $this->Request()->getParam('streamId');
        $query = Shopware()->Container()->get(Connection::class)->createQueryBuilder();

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

    /**
     * @return void
     */
    public function removeSelectedProductAction()
    {
        $streamId = $this->Request()->getParam('streamId');
        $productId = $this->Request()->getParam('articleId');

        Shopware()->Container()->get(Connection::class)->executeUpdate(
            'DELETE FROM s_product_streams_selection WHERE stream_id = :streamId AND article_id = :articleId',
            [':streamId' => $streamId, ':articleId' => $productId]
        );

        $this->View()->assign('success', true);
    }

    /**
     * @return void
     */
    public function addSelectedProductAction()
    {
        $streamId = $this->Request()->getParam('streamId');
        $productId = $this->Request()->getParam('articleId');

        Shopware()->Container()->get(Connection::class)->executeUpdate(
            'INSERT IGNORE INTO s_product_streams_selection(stream_id, article_id) VALUES (:streamId, :articleId)',
            [':streamId' => $streamId, ':articleId' => $productId]
        );

        $this->View()->assign('success', true);
    }

    /**
     * @return void
     */
    public function getAttributesAction()
    {
        $service = Shopware()->Container()->get(CrudService::class);
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
                'type' => $struct->getColumnType(),
            ];
        }

        $this->View()->assign([
            'success' => true,
            'data' => $columns,
            'total' => \count($data),
        ]);
    }

    /**
     * @return void
     */
    public function copySelectedProductsAction()
    {
        $sourceStreamId = $this->Request()->getParam('sourceStreamId', false);
        $targetStreamId = $this->Request()->getParam('targetStreamId', false);

        if ($sourceStreamId === $targetStreamId || !$sourceStreamId || !$targetStreamId) {
            return;
        }

        Shopware()->Container()->get(Connection::class)->executeUpdate(
            'INSERT IGNORE INTO s_product_streams_selection (stream_id, article_id) SELECT :targetStreamId, article_id FROM s_product_streams_selection WHERE stream_id = :sourceStreamId',
            [':targetStreamId' => $targetStreamId, ':sourceStreamId' => $sourceStreamId]
        );

        $this->View()->assign('success', true);
    }

    protected function getSortConditions($sort, $model, $alias, $whiteList = [])
    {
        $sort = parent::getSortConditions($sort, $model, $alias, $whiteList);

        if ($this->isSortingSetByUser($sort)) {
            return $sort;
        }

        return $this->addSortingById($sort);
    }

    /**
     * @throws ModelNotFoundException if the specified shop couldn't be found
     */
    private function createContext(int $shopId, int $currencyId, ?string $customerGroupKey = null): ShopContextInterface
    {
        $repo = Shopware()->Container()->get(ModelManager::class)->getRepository(Shop::class);

        $shop = $repo->getActiveById($shopId);

        if (!$shop instanceof Shop) {
            throw new ModelNotFoundException(Shop::class, $shopId);
        }

        $shopId = $shop->getId();

        if ($currencyId === 0) {
            $currencyId = $shop->getCurrency()->getId();
        }

        if (!$customerGroupKey) {
            $customerGroupKey = ContextService::FALLBACK_CUSTOMER_GROUP;
        }

        return Shopware()->Container()->get(ContextServiceInterface::class)
            ->createShopContext($shopId, $currencyId, $customerGroupKey);
    }

    /**
     * @param array<array{property: string, direction: string}> $sort
     */
    private function isSortingSetByUser(array $sort): bool
    {
        return \count($sort) > 1;
    }

    /**
     * @param array<array{property: string, direction: string}> $sort
     *
     * @return array<array{property: string, direction: string}>
     */
    private function addSortingById(array $sort): array
    {
        $sort[] = [
            'property' => $this->alias . '.id',
            'direction' => 'ASC',
        ];

        return $sort;
    }
}
