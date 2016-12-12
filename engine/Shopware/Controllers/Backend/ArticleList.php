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
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;

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
     * @var Shopware\Models\MultiEdit\Repository
     */
    protected $multiEditRepository;

    /**
     * Registers the different acl permission for the different controller actions.
     *
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
        $this->addAclPermission('getGrammarAction', 'createFilters', 'Insufficient Permissions');
        $this->addAclPermission('getValuesAction', 'createFilters', 'Insufficient Permissions');
        $this->addAclPermission('saveFilterAction', 'editFilters', 'Insufficient Permissions');
        $this->addAclPermission('deleteFilterAction', 'deleteFilters', 'Insufficient Permissions');
        $this->addAclPermission('createQueueAction', 'doMultiEdit', 'Insufficient Permissions');
    }

    /**
     * @return \Shopware\Models\MultiEdit\Repository
     */
    public function getMultiEditRepository()
    {
        if (!isset($this->multiEditRepository)) {
            $this->multiEditRepository = Shopware()->Models()->getRepository('Shopware\Models\MultiEdit\Filter');
        }

        return $this->multiEditRepository;
    }

    /**
     * Returns an array with all the columns the user is able to show / edit and their configuration
     */
    public function columnConfigAction()
    {
        $resourceName = $this->Request()->getParam('resource');

        /** @var \Shopware\Components\MultiEdit\Resource\ResourceInterface $resource */
        $resource = $this->get('multi_edit.' . $resourceName);
        $data = $resource->getColumnConfig();

        $this->View()->assign(array(
            'success' => true,
            'data' => $data
        ));
    }

    /**
     * Called when a single entity (=> product) is stored
     */
    public function saveSingleEntityAction()
    {
        $params = $this->Request()->getParams();
        $resource = $this->Request()->getParam('resource');

        // Sort out context params
        foreach ($params as $key => &$value) {
            if (preg_match('/[0-9a-z]+_[0-9a-z]+/i', $key) != 1) {
                unset($params[$key]);
                continue;
            }
            list($entity, $field) = explode('_', $key);
            $value = array('entity' => $entity, 'field' => $field, 'value' => $value);
        }

        /** @var \Shopware\Components\MultiEdit\Resource\ResourceInterface $resource */
        $resource = $this->get('multi_edit.' . $resource);
        $data = $resource->save($params);

        $this->View()->assign(array(
            'success' => true,
            'data' => $data
        ));
    }


    /**
     * Backup related operations
     */

    /**
     * Controller action for deleting a given plugin
     */
    public function deleteAction()
    {
        $resource = $this->Request()->getParam('resource');
        $id = $this->Request()->getParam('id');

        /** @var \Shopware\Components\MultiEdit\Resource\ResourceInterface $resource */
        $resource = $this->get('multi_edit.' . $resource);
        $success = $resource->deleteBackup($id);

        $this->View()->assign(array(
            'success' => $success
        ));
    }

    /**
     * Controller action for restoring a given backup
     */
    public function restoreAction()
    {
        $resource = $this->Request()->getParam('resource');
        $id = $this->Request()->getParam('id');
        $offset = $this->Request()->getParam('offset');

        /** @var \Shopware\Components\MultiEdit\Resource\ResourceInterface $resource */
        $resource = $this->get('multi_edit.' . $resource);
        $data = $resource->restoreBackup($id, $offset);

        $this->View()->assign(array(
            'success' => true,
            'data' => $data
        ));
    }


    /**
     * Returns the backups available for the current resource
     */
    public function listAction()
    {
        $resource = $this->Request()->getParam('resource');
        $limit = $this->Request()->getParam('limit', 25);
        $offset = ($this->Request()->getParam('page', 1) - 1) * $limit;

        /** @var \Shopware\Components\MultiEdit\Resource\ResourceInterface $resource */
        $resource = $this->get('multi_edit.' . $resource);
        $result = $resource->listBackups($offset, $limit);
        $result['success'] = true;

        $this->View()->assign($result);
    }


    /**
     * Batch process related operations
     */

    /**
     * Currently returns an array of an hardcoded default operation. Might be use for storing operations in the future
     */
    public function getOperationsAction()
    {
        $data = array(
            array()
        );

        $this->View()->assign(
            array(
                'success' => true,
                'data' => $data
            )
        );
    }

    /**
     * Returns an array of operators
     */
    public function getOperatorsAction()
    {
        $resource = $this->Request()->getParam('resource');

        /** @var \Shopware\Components\MultiEdit\Resource\ResourceInterface $resource */
        $resource = $this->get('multi_edit.' . $resource);
        $columns = $resource->getBatchColumns();

        // Extract the operators and build array for the extjs store
        $operators = array();
        foreach ($columns as $columnOperators) {
            foreach ($columnOperators as $operator) {
                if (!array_key_exists($operator, $operators)) {
                    $operators[$operator] = array('name' => $operator);
                }
            }
        }

        $this->View()->assign(
            array(
                'success' => true,
                'data' => array_values($operators)
            )
        );
    }

    /**
     * Returns a list of columns the user is able to edit
     */
    public function getEditableColumnsAction()
    {
        $resource = $this->Request()->getParam('resource');

        /** @var \Shopware\Components\MultiEdit\Resource\ResourceInterface $resource */
        $resource = $this->get('multi_edit.' . $resource);
        $columns = $resource->getBatchColumns();

        ksort($columns);

        // yo dawg i heard you like functional programming…
        // Prepare the returned array structure for extjs
        $columns = array_map(
            function ($column, $operators) {
                $operators = array_map(
                    function ($operator, $id) {
                        return array('id' => $id, 'name' => $operator);
                    },
                    $operators,
                    array_keys($operators)
                );

                return array('name' => $column, 'operators' => $operators);
            },
            array_keys($columns),
            $columns
        );

        $this->View()->assign(
            array(
                'success' => true,
                'data' => $columns
            )
        );
    }

    /**
     * Runs the batch process
     */
    public function batchAction()
    {
        $resource = $this->Request()->getParam('resource');

        $queueId = $this->Request()->getParam('queueId', null);

        /** @var \Shopware\Components\MultiEdit\Resource\ResourceInterface $resource */
        $resource = $this->get('multi_edit.' . $resource);
        $data = $resource->batchProcess($queueId);

        $this->View()->assign(
            array(
                'success' => true,
                'data' => $data
            )
        );
    }


    /**
     * Filter related operations
     */


    /**
     * Controller action which will return the grammar of the requested resource
     */
    public function getGrammarAction()
    {
        $resource = $this->Request()->getParam('resource');

        /** @var \Shopware\Components\MultiEdit\Resource\ResourceInterface $resource */
        $resource = $this->get('multi_edit.' . $resource);
        $grammar = $resource->getGrammar();

        $this->View()->assign(
            array(
                'success' => true,
                'data' => $grammar
            )
        );
    }

    /**
     * Controller action that will return suggested values for a given resource and a given attribute
     */
    public function getValuesAction()
    {
        $resource = $this->Request()->getParam('resource');
        $attribute = $this->Request()->getParam('attribute');
        $operator = $this->Request()->getParam('operator');

        // The offset needs to be decreased by one as ExtJs starts counting by 1
        $limit = $this->Request()->getParam('limit', 25);
        $offset = ($this->Request()->getParam('page', 1) - 1) * $limit;
        $filter = $this->Request()->getParam('filter', array());
        $filter = isset($filter[0]['value']) ? $filter[0]['value'] : null;
        if (!$filter) {
            $filter = $this->Request()->getParam('query', null);
        }

        /** @var \Shopware\Components\MultiEdit\Resource\ResourceInterface $resource */
        $resource = $this->get('multi_edit.' . $resource);
        $data = $resource->getValuesFor(
            $attribute,
            $operator,
            array('offset' => $offset, 'limit' => $limit, 'filter' => $filter)
        );
        $data['success'] = true;

        $this->View()->assign($data);
    }

    /**
     * Controller action which filters the products
     */
    public function filterAction()
    {
        $resource = $this->Request()->getParam('resource');
        $ast = $this->Request()->getParam('ast');
        $limit = $this->Request()->getParam('limit', 25);
        $offset = ($this->Request()->getParam('page', 1) - 1) * $limit;
        $sort = $this->Request()->getParam('sort', array());

        if (!empty($sort)) {
            $sort = array_pop($sort);
        }

        $ast = json_decode($ast, true);
        if ($ast == false) {
            throw new RuntimeException('Could not decode AST');
        }

        /** @var \Shopware\Components\MultiEdit\Resource\ResourceInterface $resource */
        $resource = $this->get('multi_edit.' . $resource);
        $result = $resource->filter($ast, $offset, $limit, $sort);

        if ($this->displayVariants($ast)) {
            $result = $this->addAdditionalText($result);
        }

        $this->View()->assign(
            array(
                'success' => true,
                'data' => $result['data'],
                'total' => $result['total']
            )
        );
    }

    /**
     * Normalize filter
     *
     * @param $filter
     * @return string
     */
    private function normalizeFilter($filter)
    {
        return strtolower(preg_replace('/[^\da-z]/i', '_', strip_tags($filter)));
    }

    /**
     * Translate filter name and description
     *
     * @param $filter
     * @return mixed
     */
    private function translateFilter($filter)
    {
        $name = 'filterName-' . $this->normalizeFilter($filter['name']);
        $description = 'filterDescription-' . $this->normalizeFilter($filter['description']);

        $namespace = Shopware()->Snippets()->getNamespace('backend/article_list/main');
        $filter['name'] = $namespace->get($name, $filter['name']);
        $filter['description'] = $namespace->get($description, $filter['description']);

        return $filter;
    }

    /**
     * Returns a list of all available filters
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
            array(
                'success' => true,
                'data' => $results
            )
        );
    }

    /**
     * Save/update a given filter. Also called when a filter is (un)favorited
     */
    public function saveFilterAction()
    {
        $data = $this->Request()->getParams();
        $id = $this->Request()->get('id', null);

        if ($id) {
            $filter = $this->getMultiEditRepository()->find($id);
        } else {
            $filter = new Shopware\Models\MultiEdit\Filter();
        }

        $filter->fromArray($data);

        Shopware()->Models()->persist($filter);
        Shopware()->Models()->flush();

        $this->View()->assign(
            array(
                'success' => true,
            )
        );
    }

    /**
     * Delete a given filter
     */
    public function deleteFilterAction()
    {
        $id = $this->Request()->get('id');

        $filter = Shopware()->Models()->find('Shopware\Models\MultiEdit\Filter', $id);

        if ($filter) {
            Shopware()->Models()->remove($filter);
            Shopware()->Models()->flush();
        }

        $this->View()->assign(
            array(
                'success' => true,
            )
        );
    }

    /**
     * Queue related operations
     */

    /**
     * Controller action called while the batch queue is created
     *
     * @throws RuntimeException
     */
    public function createQueueAction()
    {
        $resource = $this->Request()->getParam('resource');
        $filterArray = $this->Request()->getParam('filterArray');
        $operations = $this->Request()->getParam('operations');
        $limit = $this->Request()->getParam('limit', 1000);
        $queueId = $this->Request()->getParam('queueId', null);
        $offset = $this->Request()->getParam('offset', 0);
        $filterArray = json_decode($filterArray, true);
        if ($filterArray == false) {
            throw new RuntimeException("Could not decode '{$this->Request()->getParam('filterArray')}'");
        }

        $operations = json_decode($operations, true);
        if ($operations == false) {
            throw new RuntimeException("Could not decode '{$this->Request()->getParam('operations')}'");
        }

        /** @var \Shopware\Components\MultiEdit\Resource\ResourceInterface $resource */
        $resource = $this->get('multi_edit.' . $resource);
        $return = $resource->createQueue($filterArray, $operations, $offset, $limit, $queueId);

        $this->View()->assign(
            array(
                'success' => true,
                'data' => $return
            )
        );
    }

    /**
     * Internal helper function to get access to the article repository.
     *
     * @return \Shopware\Components\Model\ModelRepository
     */
    private function getDetailRepository()
    {
        return Shopware()->Models()->getRepository('Shopware\Models\Article\Detail');
    }

    /**
     * Event listener function of the article store of the backend module.
     *
     * @return mixed
     */
    public function deleteProductAction()
    {
        $id = (int) $this->Request()->getParam('Detail_id');

        /** @var $articleDetail \Shopware\Models\Article\Detail   */
        $articleDetail = $this->getDetailRepository()->find($id);
        if (!is_object($articleDetail)) {
            $this->View()->assign(array(
                'success' => false
            ));
        } else {
            $articleResource = new Shopware\Components\Api\Resource\Article();
            $articleResource->setManager($this->get('models'));

            if ($articleDetail->getKind() == 1) {
                $articleResource->delete($articleDetail->getArticle()->getId());
            } else {
                Shopware()->Models()->remove($articleDetail);
            }

            Shopware()->Models()->flush();

            $this->View()->assign(array(
                'success' => true
            ));
        }
    }

    /**
     * @param array[] $result
     * @return array[]
     */
    private function addAdditionalText($result)
    {
        $products = $this->buildListProducts($result['data']);

        $products = $this->getAdditionalTexts($products);

        foreach ($result['data'] as &$item) {
            $number = $item['Detail_number'];
            $item['Detail_additionalText_dynamic'] = null;
            if (!isset($products[$number])) {
                continue;
            }
            $product = $products[$number];
            $item['Detail_additionalText_dynamic'] = $product->getAdditional();
        }
        return $result;
    }

    /**
     * @param array[] $result
     * @return ListProduct[]
     */
    private function buildListProducts($result)
    {
        $products = [];
        foreach ($result as $item) {
            $number = $item['Detail_number'];

            $product = new \Shopware\Bundle\StoreFrontBundle\Struct\ListProduct(
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
     * @return bool
     */
    private function displayVariants($ast)
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
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\ListProduct[] $products
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\ListProduct[]
     */
    private function getAdditionalTexts($products)
    {
        /** @var \Shopware\Bundle\StoreFrontBundle\Service\AdditionalTextServiceInterface $service */
        $service = $this->get('shopware_storefront.additional_text_service');

        /** @var \Shopware\Models\Shop\Repository $shopRepo */
        $shopRepo = $this->get('models')->getRepository('Shopware\Models\Shop\Shop');

        /** @var \Shopware\Models\Shop\Shop $shop */
        $shop = $shopRepo->getActiveDefault();

        /** @var \Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface $contextService */
        $contextService = $this->get('shopware_storefront.context_service');

        $context = $contextService->createShopContext(
            $shop->getId(),
            $shop->getCurrency()->getId(),
            ContextService::FALLBACK_CUSTOMER_GROUP
        );

        return $service->buildAdditionalTextLists($products, $context);
    }
}
