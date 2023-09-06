<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

use Shopware\Bundle\AttributeBundle\Repository\ProductRepository;
use Shopware\Bundle\AttributeBundle\Repository\SearchCriteria;
use Shopware\Bundle\StoreFrontBundle\Service\AdditionalTextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Components\Api\Resource\Article as ArticleResource;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\MultiEdit\Resource\ResourceInterface;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Repository as ProductModelRepository;
use Shopware\Models\MultiEdit\Filter;
use Shopware\Models\MultiEdit\Repository;
use Shopware\Models\Shop\Shop;

/**
 * Shopware SwagMultiEdit Plugin - MultiEdit Backend Controller
 *
 * Loads the ExtJS application and some configuration requests
 */
class Shopware_Controllers_Backend_ArticleList extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Reference to the SwagMultiEdit repository
     *
     * @var Repository|null
     */
    protected $multiEditRepository;

    /**
     * Registers the different acl permission for the different controller actions.
     */
    public function initAcl()
    {
        $this->addAclPermission('saveSingleEntityAction', 'editSingleArticle', 'Insufficient Permissions');
        $this->addAclPermission('deleteAction', 'doBackup', 'Insufficient Permissions');
        $this->addAclPermission('restoreAction', 'doBackup', 'Insufficient Permissions');
        $this->addAclPermission('getOperationsAction', 'doMultiEdit', 'Insufficient Permissions');
        $this->addAclPermission('getOperatorsAction', 'doMultiEdit', 'Insufficient Permissions');
        $this->addAclPermission('getEditableColumnsAction', 'doMultiEdit', 'Insufficient Permissions');
        $this->addAclPermission('batchAction', 'doMultiEdit', 'Insufficient Permissions');
        $this->addAclPermission('getGrammarAction', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getValuesAction', 'read', 'Insufficient Permissions');
        $this->addAclPermission('saveFilterAction', 'editFilters', 'Insufficient Permissions');
        $this->addAclPermission('deleteFilterAction', 'deleteFilters', 'Insufficient Permissions');
        $this->addAclPermission('createQueueAction', 'doMultiEdit', 'Insufficient Permissions');
    }

    /**
     * @return Repository
     */
    public function getMultiEditRepository()
    {
        if (!isset($this->multiEditRepository)) {
            $this->multiEditRepository = $this->get('models')->getRepository('Shopware\Models\MultiEdit\Filter');
        }

        return $this->multiEditRepository;
    }

    /**
     * Returns an array with all the columns the user is able to show / edit and their configuration
     *
     * @retrun void
     */
    public function columnConfigAction()
    {
        $resourceName = $this->Request()->getParam('resource');

        /** @var ResourceInterface $resource */
        $resource = $this->container->get('multi_edit.' . $resourceName);
        $data = $resource->getColumnConfig();

        $this->View()->assign([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Called when a single entity (=> product) is stored
     *
     * @retrun void
     */
    public function saveSingleEntityAction()
    {
        $params = $this->Request()->getParams();
        $resource = $this->Request()->getParam('resource');

        // Sort out context params
        foreach ($params as $key => &$value) {
            if (preg_match('/[0-9a-z]+_[0-9a-z]+/i', $key) !== 1) {
                unset($params[$key]);
                continue;
            }
            [$entity, $field] = explode('_', $key);
            $value = ['entity' => $entity, 'field' => $field, 'value' => $value];
        }

        /** @var ResourceInterface $resource */
        $resource = $this->container->get('multi_edit.' . $resource);
        $data = $resource->save($params);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Controller action for deleting a given plugin
     *
     * @retrun void
     */
    public function deleteAction()
    {
        $resource = $this->Request()->getParam('resource');
        $id = $this->Request()->getParam('id');

        /** @var ResourceInterface $resource */
        $resource = $this->container->get('multi_edit.' . $resource);
        $success = $resource->deleteBackup($id);

        $this->View()->assign([
            'success' => $success,
        ]);
    }

    /**
     * Controller action for restoring a given backup
     *
     * @retrun void
     */
    public function restoreAction()
    {
        $resource = $this->Request()->getParam('resource');
        $id = \is_null($this->Request()->getParam('id')) ? null : (int) $this->Request()->getParam('id');

        if (!\is_int($id)) {
            throw new \DomainException('No ID was provided');
        }

        $offset = (int) $this->Request()->getParam('offset', 0);

        /** @var ResourceInterface $resource */
        $resource = $this->container->get('multi_edit.' . $resource);
        $data = $resource->restoreBackup($id, $offset);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Returns the backups available for the current resource
     *
     * @retrun void
     */
    public function listAction()
    {
        $resourceClass = $this->Request()->getParam('resource');
        $limit = $this->Request()->getParam('limit', 25);
        $offset = ($this->Request()->getParam('page', 1) - 1) * $limit;

        /** @var ResourceInterface $resource */
        $resource = $this->container->get('multi_edit.' . $resourceClass);
        $result = $resource->listBackups($offset, $limit);
        $result['success'] = true;

        $this->View()->assign($result);
    }

    /**
     * Currently returns an array of a hardcoded default operation. Might be use for storing operations in the future
     *
     * @return void
     */
    public function getOperationsAction()
    {
        $data = [
            [],
        ];

        $this->View()->assign(
            [
                'success' => true,
                'data' => $data,
            ]
        );
    }

    /**
     * Returns an array of operators
     *
     * @retrun void
     */
    public function getOperatorsAction()
    {
        $resource = $this->Request()->getParam('resource');

        /** @var ResourceInterface $resource */
        $resource = $this->container->get('multi_edit.' . $resource);
        $columns = $resource->getBatchColumns();

        // Extract the operators and build array for the extjs store
        $operators = [];
        foreach ($columns as $columnOperators) {
            foreach ($columnOperators as $operator) {
                if (!\array_key_exists($operator, $operators)) {
                    $operators[$operator] = ['name' => $operator];
                }
            }
        }

        $this->View()->assign(
            [
                'success' => true,
                'data' => array_values($operators),
            ]
        );
    }

    /**
     * Returns a list of columns the user is able to edit
     *
     * @retrun void
     */
    public function getEditableColumnsAction()
    {
        $resource = $this->Request()->getParam('resource');

        /** @var ResourceInterface $resource */
        $resource = $this->container->get('multi_edit.' . $resource);
        $columns = $resource->getBatchColumns();

        ksort($columns);

        // Prepare the returned array structure for extjs
        $columns = array_map(
            function ($column, $operators) {
                $operators = array_map(
                    function ($operator, $id) {
                        return ['id' => $id, 'name' => $operator];
                    },
                    $operators,
                    array_keys($operators)
                );

                return ['name' => $column, 'operators' => $operators];
            },
            array_keys($columns),
            $columns
        );

        $this->View()->assign(
            [
                'success' => true,
                'data' => $columns,
            ]
        );
    }

    /**
     * Runs the batch process
     *
     * @retrun void
     */
    public function batchAction()
    {
        $resource = $this->Request()->getParam('resource');

        $queueId = $this->Request()->getParam('queueId');

        /** @var ResourceInterface $resource */
        $resource = $this->container->get('multi_edit.' . $resource);
        $data = $resource->batchProcess($queueId);

        $this->View()->assign(
            [
                'success' => true,
                'data' => $data,
            ]
        );
    }

    /**
     * Controller action which will return the grammar of the requested resource
     *
     * @retrun void
     */
    public function getGrammarAction()
    {
        $resource = $this->Request()->getParam('resource');

        /** @var ResourceInterface $resource */
        $resource = $this->container->get('multi_edit.' . $resource);
        $grammar = $resource->getGrammar();

        $this->View()->assign(
            [
                'success' => true,
                'data' => $grammar,
            ]
        );
    }

    /**
     * Controller action that will return suggested values for a given resource and a given attribute
     *
     * @retrun void
     */
    public function getValuesAction()
    {
        $resource = $this->Request()->getParam('resource');
        $attribute = $this->Request()->getParam('attribute');
        $operator = $this->Request()->getParam('operator');

        // The offset needs to be decreased by one as ExtJs starts counting by 1
        $limit = $this->Request()->getParam('limit', 25);
        $offset = ($this->Request()->getParam('page', 1) - 1) * $limit;
        $filter = $this->Request()->getParam('filter', []);
        $filter = isset($filter[0]['value']) ? $filter[0]['value'] : null;
        if (!$filter) {
            $filter = $this->Request()->getParam('query');
        }

        /** @var ResourceInterface $resource */
        $resource = $this->container->get('multi_edit.' . $resource);
        $data = $resource->getValuesFor(
            $attribute,
            $operator,
            ['offset' => $offset, 'limit' => $limit, 'filter' => $filter]
        );
        $data['success'] = true;

        $this->View()->assign($data);
    }

    /**
     * Controller action which filters the products
     *
     * @retrun void
     */
    public function filterAction()
    {
        $resource = $this->Request()->getParam('resource');
        $ast = $this->Request()->getParam('ast');
        $limit = $this->Request()->getParam('limit', 25);
        $offset = ($this->Request()->getParam('page', 1) - 1) * $limit;
        $sort = $this->Request()->getParam('sort', []);

        if (!empty($sort)) {
            $sort = array_pop($sort);
        }

        $ast = json_decode($ast, true);
        if ($ast == false) {
            throw new RuntimeException('Could not decode AST');
        }

        if ($this->container->getParameter('shopware.es.backend.enabled')) {
            $result = $this->filterByRepository();
            $this->View()->assign([
                'success' => true,
                'data' => $result['data'],
                'total' => $result['total'],
            ]);

            return;
        }

        /** @var ResourceInterface $resource */
        $resource = $this->container->get('multi_edit.' . $resource);
        $result = $resource->filter($ast, $offset, $limit, $sort);

        if ($this->displayVariants($ast)) {
            $result = $this->addAdditionalText($result);
        }

        $this->View()->assign(
            [
                'success' => true,
                'data' => $result['data'],
                'total' => $result['total'],
            ]
        );
    }

    /**
     * Returns a list of all available filters
     *
     * @retrun void
     */
    public function getFilterAction()
    {
        $repository = $this->getMultiEditRepository();
        $query = $repository->getListQuery();
        $results = $query->getArrayResult();

        foreach ($results as &$filter) {
            $filter = $this->translateFilter($filter);
        }

        $this->View()->assign(
            [
                'success' => true,
                'data' => $results,
            ]
        );
    }

    /**
     * Save/update a given filter. Also called when a filter is (un)favored
     *
     * @retrun void
     */
    public function saveFilterAction()
    {
        $data = $this->Request()->getParams();
        $id = \is_null($this->Request()->getParam('id')) ? null : (int) $this->Request()->getParam('id');

        $filter = null;
        if (\is_int($id)) {
            $filter = $this->getMultiEditRepository()->find($id);
        }
        if (!$filter instanceof Filter) {
            $filter = new Filter();
        }

        $filter->fromArray($data);

        $this->get('models')->persist($filter);
        $this->get('models')->flush();

        $this->View()->assign(
            [
                'success' => true,
            ]
        );
    }

    /**
     * Delete a given filter
     *
     * @retrun void
     */
    public function deleteFilterAction()
    {
        $id = $this->Request()->get('id');

        $filter = $this->get('models')->find(Filter::class, $id);

        if ($filter) {
            $this->get('models')->remove($filter);
            $this->get('models')->flush();
        }

        $this->View()->assign(
            [
                'success' => true,
            ]
        );
    }

    /**
     * Queue related operations
     */

    /**
     * Controller action called while the batch queue is created
     *
     * @retrun void
     */
    public function createQueueAction()
    {
        $resource = $this->Request()->getParam('resource');
        $filterArray = $this->Request()->getParam('filterArray');
        $operations = $this->Request()->getParam('operations');
        $limit = $this->Request()->getParam('limit', 1000);
        $queueId = $this->Request()->getParam('queueId');
        $offset = $this->Request()->getParam('offset', 0);
        $filterArray = json_decode($filterArray, true);
        if ($filterArray === false) {
            throw new RuntimeException(sprintf('Could not decode "%s"', $this->Request()->getParam('filterArray')));
        }

        /** @var array|false $operations */
        $operations = json_decode($operations, true);
        if ($operations === false) {
            throw new RuntimeException(sprintf('Could not decode "%s"', $this->Request()->getParam('operations')));
        }

        /** @var ResourceInterface $resource */
        $resource = $this->container->get('multi_edit.' . $resource);
        $return = $resource->createQueue($filterArray, $operations, $offset, $limit, $queueId);

        $this->View()->assign(
            [
                'success' => true,
                'data' => $return,
            ]
        );
    }

    /**
     * Event listener function of the product store of the backend module.
     *
     * @retrun void
     */
    public function deleteProductAction()
    {
        $id = \is_null($this->Request()->getParam('Detail_id')) ? null : (int) $this->Request()->getParam('Detail_id');

        /** @var Detail $variant */
        $variant = $this->getDetailRepository()->find($id);
        if (!\is_object($variant)) {
            $this->View()->assign([
                'success' => false,
            ]);
        } else {
            $articleResource = new ArticleResource();
            $articleResource->setManager($this->get(ModelManager::class));

            if ($variant->getKind() == 1) {
                $articleResource->delete($variant->getArticle()->getId());
            } else {
                $this->get('models')->remove($variant);
            }

            $this->get('models')->flush();

            $this->View()->assign([
                'success' => true,
            ]);
        }
    }

    private function normalizeFilter(string $filter): string
    {
        return strtolower((string) preg_replace('/[^\da-z]/i', '_', strip_tags($filter)));
    }

    /**
     * Translate filter name and description
     *
     * @param array<string, string> $filter
     *
     * @return array<string, string>
     */
    private function translateFilter(array $filter): array
    {
        $name = 'filterName-' . $this->normalizeFilter($filter['name']);
        $description = 'filterDescription-' . $this->normalizeFilter($filter['description']);

        $namespace = Shopware()->Snippets()->getNamespace('backend/article_list/main');
        $filter['name'] = $namespace->get($name, $filter['name']);
        $filter['description'] = $namespace->get($description, $filter['description']);

        return $filter;
    }

    /**
     * Internal helper function to get access to the product repository.
     */
    private function getDetailRepository(): ProductModelRepository
    {
        return $this->get('models')->getRepository(Detail::class);
    }

    /**
     * @param array{data: array<array<string, mixed>>, total: int} $result
     *
     * @return array{data: array<array<string, mixed>>, total: int}
     */
    private function addAdditionalText(array $result): array
    {
        $products = $this->buildListProducts($result['data']);

        $products = $this->getAdditionalTexts($products);

        foreach ($result['data'] as &$item) {
            $number = $item['Detail_number'];
            $item['Detail_additionalText_dynamic'] = null;
            if (!isset($products[$number])) {
                continue;
            }
            $item['Detail_additionalText_dynamic'] = $products[$number]->getAdditional();
        }

        return $result;
    }

    /**
     * @param array<array<string, mixed>> $result
     *
     * @return array<string, ListProduct>
     */
    private function buildListProducts(array $result): array
    {
        $products = [];
        foreach ($result as $item) {
            $number = (string) $item['Detail_number'];

            $product = new ListProduct(
                $item['Article_id'],
                $item['Detail_id'],
                $item['Detail_number']
            );
            if ($item['Detail_additionalText']) {
                $product->setAdditional($item['Detail_additionalText']);
            }
            $products[$number] = $product;
        }

        return $products;
    }

    /**
     * @param array[] $ast
     */
    private function displayVariants($ast): bool
    {
        foreach ($ast as $filter) {
            if (!isset($filter['token'])) {
                continue;
            }
            if ($filter['token'] === 'ISMAIN') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param ListProduct[] $products
     *
     * @return ListProduct[]
     */
    private function getAdditionalTexts(array $products): array
    {
        $service = $this->get(AdditionalTextServiceInterface::class);

        $shopRepo = $this->get(ModelManager::class)->getRepository(Shop::class);

        $shop = $shopRepo->getActiveDefault();

        $contextService = $this->get(ContextServiceInterface::class);

        $context = $contextService->createShopContext(
            $shop->getId(),
            $shop->getCurrency()->getId(),
            ContextService::FALLBACK_CUSTOMER_GROUP
        );

        return $service->buildAdditionalTextLists($products, $context);
    }

    /**
     * @return array{data: array, total: int}
     */
    private function filterByRepository(): array
    {
        $criteria = $this->createCriteria($this->Request());

        if ($this->Request()->getParam('showVariants', 'false') === 'false') {
            $criteria->conditions[] = [
                'property' => 'kind',
                'value' => 1,
            ];
        }

        $repository = $this->container->get(ProductRepository::class);

        $result = $repository->search($criteria);

        $ids = array_column($result->getData(), 'variantId');

        $data = $this->container->get('multi_edit.product.dql_helper')
            ->getProductsForListing($ids);

        $sortedData = [];
        foreach ($ids as $id) {
            foreach ($data as $key => $row) {
                if ($row['Detail_id'] == $id) {
                    $sortedData[] = $row;
                    unset($data[$key]);
                    break;
                }
            }
        }

        return ['data' => $sortedData, 'total' => $result->getCount()];
    }

    private function createCriteria(Enlight_Controller_Request_Request $request): SearchCriteria
    {
        if ($request->getParam('showVariants', false) === 'true') {
            $criteria = new SearchCriteria(Detail::class);
        } else {
            $criteria = new SearchCriteria(Article::class);
        }
        $criteria->offset = $request->getParam('start', 0);
        $criteria->limit = $request->getParam('limit', 30);
        $criteria->ids = $request->getParam('ids', []);
        $criteria->term = $request->getParam('query', null);
        $criteria->sortings = $request->getParam('sort', []);
        $criteria->conditions = $request->getParam('filters', []);

        $categoryId = (int) $request->getParam('categoryId', 0);

        if ($categoryId > 0) {
            $criteria->conditions[] = ['property' => 'categoryIds', 'value' => $categoryId, 'expression' => 'IN'];
        }

        foreach ($criteria->sortings as $index => &$sorting) {
            switch ($sorting['property']) {
                case 'Detail_number':
                    $sorting['property'] = 'number';
                    break;
                case 'Article_name':
                    $sorting['property'] = 'name.raw';
                    break;
                case 'Supplier_name':
                    $sorting['property'] = 'supplierName.raw';
                    break;
                case 'Article_active':
                    $sorting['property'] = 'articleActive';
                    break;
                case 'Tax_name':
                    $sorting['property'] = 'taxId';
                    break;
                case 'Detail_inStock':
                    $sorting['property'] = 'inStock';
                    break;
                case 'Price_price':
                    $sorting['property'] = 'price';
                    break;
                default:
                    unset($criteria->sortings[$index]);
                    break;
            }
        }

        $criteria->params = $request->getParams();

        if (!empty($criteria->ids)) {
            $criteria->ids = json_decode($criteria->ids, true);
        }

        return $criteria;
    }
}
