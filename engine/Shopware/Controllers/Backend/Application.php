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

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Shopware\Components\Model\QueryBuilder;

/**
 * Base controller for a single backend sub application.
 * This controller contains many functions for the quad operations for a single model
 * The Shopware_Controllers_Backend_Application can only be used if the application works
 * with the Shopware Doctrine models.
 * Otherwise the controller functions throws different exception that the model has to be configured.
 * In this case use the Shopware_Controllers_Backend_ExtJs controller for your backend application.
 *
 * How to use:
 *  - Create a new backend controller with the name of your backend application
 *      Example:
 *      - Your backend application is called "Shopware.apps.Product"
 *      - So create a new backend php controller "Shopware_Controllers_Backend_Product"
 *  - The only thing you have to do now is to configure the doctrine model in the $model class property.
 *  - For example $model = 'Shopware\Models\Article\Article'
 *  - After you have configured the model property, the whole backend application works.
 *   - Loading an filtered, sorted and paginated list of your models - listAction().
 *   - Loading the detailed data of a single model - detailAction().
 *   - Create a new model - createAction()
 *   - Update an existing model - updateAction()
 *   - Delete an existing model - deleteAction()
 *
 * Structure information:
 *  - All model data are selected over the \Shopware\Components\Model\QueryBuilder.
 *  - Functions which selects model data are suffixed with "...Query".
 *
 * Additional configuration:
 *  - The backend controller supports additional configuration for the listing or detail actions.
 *  - For example you can limit the sortable fields by using the $sortFields property
 *  - Or you can limit the filterable fields by using the $filterFields property.
 */
abstract class Shopware_Controllers_Backend_Application extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Contains the repository class of the configured
     * doctrine model.
     *
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $repository;

    /**
     * Contains the global shopware entity manager.
     * The manager is used for each doctrine entity operation.
     *
     * @var \Shopware\Components\Model\ModelManager
     */
    protected $manager;

    /**
     * The model property is the only required class property.
     * If this property isn't set, the whole backend application don't works.
     * To configure this property you have only to set the whole model class name into this parameter.
     *
     * @example
     * $model = 'Shopware\Models\Article\Article'
     *
     * @required
     *
     * @var string model
     */
    protected $model;

    /**
     * The model alias is used for the listing and detail query builder.
     * If you want to use an own parameter please override this property.
     * Otherwise you can terminate your FROM table in the listing and detail query
     * by prefix your fields with "modelAlias.name".
     *
     * @var string
     */
    protected $alias = 'modelAlias';

    /**
     * Contains the available filter fields for the listing query.
     * If no fields configured, the listing query will be filtered with each field
     * with a fulltext or condition.
     * In order that you want to limit the filter fields, you can
     * override this property as follow:
     *
     * @example
     *  $filterFields = array('name', 'street', 'number', ...);
     *
     * Please make sure the configured sort fields matches the field names of the Ext JS model.
     *
     * @var array
     */
    protected $filterFields = [];

    /**
     * Contains the available sort fields for the listing query.
     * If no fields configured, the listing query allows to sort the result with each model field.
     *
     * In order that you want to limit the sort fields, you can
     * override this property as follow:
     *
     * @example
     *  $sortFields = array('articleName', 'ordernumber', ...);
     *
     * Please make sure the configured sort fields matches the field names of the Ext JS model.
     *
     * @var array
     */
    protected $sortFields = [];

    /**
     * Initialisation of the controller.
     * Throws an exception is the model property isn't configured.
     *
     * @throws Exception
     */
    public function init()
    {
        if (empty($this->model)) {
            throw new Exception(
                'The `model` property of your PHP controller is not configured!'
            );
        }

        parent::init();
    }

    /**
     * Allows to set the repository property of this class.
     * The repository is used for find queries for the configured model.
     */
    public function setRepository(\Shopware\Components\Model\ModelRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Allows to set the manager property of this class.
     * The manager is used for each data operation with doctrine models.
     */
    public function setManager(\Shopware\Components\Model\ModelManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Returns the instance of the global shopware entity manager.
     * Used for each data operation with doctrine models.
     *
     * @return \Shopware\Components\Model\ModelManager
     */
    public function getManager()
    {
        if ($this->manager === null) {
            $this->manager = Shopware()->Models();
        }

        return $this->manager;
    }

    /**
     * Controller action which can be called over an ajax request.
     * This function is normally used for backend listings.
     * The listing will be selected over the getList function.
     *
     * The function expects the following request parameter:
     *  query - Search string which inserted in the search field.
     *  association - Doctrine property name of the association
     *  start - Pagination start value
     *  limit - Pagination limit value
     */
    public function listAction()
    {
        $this->View()->assign(
            $this->getList(
                $this->Request()->getParam('start', 0),
                $this->Request()->getParam('limit', 20),
                $this->Request()->getParam('sort', []),
                $this->Request()->getParam('filter', []),
                $this->Request()->getParams()
            )
        );
    }

    /**
     * Controller action which can be called over ajax requests.
     * This function is used to load the detailed information for a single record.
     * Shopware use this function as "detail" api call of a single {@link Shopware.data.Model}.
     * This function is only a wrapper function, the {@link #getDetail} function contains the
     * logic to get the detail data of the record.
     *
     * @internalParam $this->Request()->getParam('id')
     */
    public function detailAction()
    {
        $this->View()->assign(
            $this->getDetail(
                $this->Request()->getParam('id')
            )
        );
    }

    /**
     * Controller action to create a new record.
     * This function can be called over an ajax request.
     * This function is only a wrapper function and calls the internal
     * {@link #save} function which creates and updates the records.
     * The createAction function pass the request params as function parameter
     * to the save function.
     * The save function return value will be assigned to the template engine.
     */
    public function createAction()
    {
        $this->View()->assign(
            $this->save(
                $this->Request()->getParams()
            )
        );
    }

    /**
     * Controller action to update existing records.
     * This function can be called over an ajax request.
     * The function is only a wrapper function and calls the internal
     * {@link #save} function which creates and updates the records.
     * The updateAction function pass the request params as function parameter
     * to the save function.
     * The save function return value will be assigned to the template engine.
     */
    public function updateAction()
    {
        $this->View()->assign(
            $this->save(
                $this->Request()->getParams()
            )
        );
    }

    /**
     * Controller action delete a single record.
     * This function can be called over an ajax request.
     * The function is only a wrapper function and calls the internal
     * {@link #delete} function which deletes the single record.
     * The deleteAction pass the request id parameter as function parameter
     * to the delete function.
     * The return value of the delete function will be assigned to the template engine.
     */
    public function deleteAction()
    {
        $this->View()->assign(
            $this->delete(
                $this->Request()->getParam('id')
            )
        );
    }

    /**
     * Controller action which called to reload associated data.
     * This function is used to load @ORM\OneToMany() associations
     * which should be displayed in an own listing on the detail page.
     */
    public function reloadAssociationAction()
    {
        $this->View()->assign(
            $this->reloadAssociation(
                $this->Request()->getParam('id'),
                $this->Request()->getParam('association'),
                $this->Request()->getParam('start', 0),
                $this->Request()->getParam('limit', 20),
                $this->Request()->getParam('sort', []),
                $this->Request()->getParam('filter', [])
            )
        );
    }

    /**
     * Controller action which called to search associated data of the configured model.
     * This function is used from the {@link Shopware.form.field.Search} backend component
     * to resolve @ORM\ManyToMany() or @ORM\ManyToOne() associations in the different backend components.
     *
     * The function expects the following request parameter:
     *  query - Search string which inserted in the search field.
     *  association - Doctrine property name of the association
     *  start - Pagination start value
     *  limit - Pagination limit value
     *
     * This function is like the other controller actions only a wrapper function and calls
     * the internal searchAssociation function to find the requested data.
     */
    public function searchAssociationAction()
    {
        $this->View()->assign(
            $this->searchAssociation(
                $this->Request()->getParam('query'),
                $this->Request()->getParam('association'),
                $this->Request()->getParam('start', 0),
                $this->Request()->getParam('limit', 20),
                $this->Request()->getParam('id'),
                $this->Request()->getParam('filter', []),
                $this->Request()->getParam('sort', [])
            )
        );
    }

    /**
     * Contains the logic to get the detailed information of a single record.
     * The function expects the model identifier value as parameter.
     * To add additional data to the detailed information you can override the
     * {@link #getAdditionalDetailData} function.
     *
     * To extend the query builder object to select more detailed information,
     * you can override the {@link #getDetailQuery} function.
     *
     * @param int $id - Identifier of the doctrine model
     *
     * @return array
     */
    public function getDetail($id)
    {
        $builder = $this->getDetailQuery($id);

        $paginator = $this->getQueryPaginator($builder);
        $data = $paginator->getIterator()->current();
        if (!$data) {
            $data = [];
        }
        $data = $this->getAdditionalDetailData($data);

        return ['success' => true, 'data' => $data];
    }

    /**
     * Contains the logic to create or update an existing record.
     * If the passed $data parameter contains a filled "id" property,
     * the function executes an entity manager find query for the configured
     * model and the passed id. If the $data parameter contains no id property,
     * this function creates a new instance of the configured model.
     *
     * If you have some doctrine association in your model, or you want
     * to modify the passed data object, you can use the {@link #resolveExtJsData} function
     * to modify the data property.
     *
     * You can implement \Symfony\Component\Validator\Constraints asserts in your model
     * which will be validate in the save process.
     * If the asserts throws an exception or some fields are invalid, the function returns
     * an array like this:
     *
     * array(
     *      'success' => false,
     *      'violations' => array(
     *          array(
     *              'message' => 'Property can not be null',
     *              'property' => 'article.name'
     *          ),
     *          ...
     *      )
     * )
     *
     * If the save process was successfully, the function returns a success array with the
     * updated model data.
     *
     * @param array $data
     *
     * @return array
     */
    public function save($data)
    {
        /* @var \Shopware\Components\Model\ModelEntity $model */
        if (!empty($data['id'])) {
            $model = $this->getRepository()->find($data['id']);
        } else {
            $model = new $this->model();
            $this->getManager()->persist($model);
        }

        $data = $this->resolveExtJsData($data);
        $model->fromArray($data);

        $violations = $this->getManager()->validate($model);
        $errors = [];
        /** @var Symfony\Component\Validator\ConstraintViolation $violation */
        foreach ($violations as $violation) {
            $errors[] = [
                'message' => $violation->getMessage(),
                'property' => $violation->getPropertyPath(),
            ];
        }

        if (!empty($errors)) {
            return ['success' => false, 'violations' => $errors];
        }

        $this->getManager()->flush();

        $detail = $this->getDetail($model->getId());

        return ['success' => true, 'data' => $detail['data']];
    }

    /**
     * Internal function which deletes the configured model with the passed identifier.
     * This function is used from the {@link #deleteAction} function which can be called over an ajax request.
     * The function can returns three different states:
     *  1. array('success' => false, 'error' => 'The id parameter contains no value.')
     *   => The passed $id parameter is empty
     *  2. array('success' => false, 'error' => 'The passed id parameter exists no more.')
     *   => The passed $id parameter contains no valid id for the configured model and the entity manager find function returns no valid entity.
     *  3. array('success' => true)
     *   => Delete was successfully.
     *
     * @param int $id
     *
     * @return array
     */
    public function delete($id)
    {
        if (empty($id)) {
            return ['success' => false, 'error' => 'The id parameter contains no value.'];
        }

        $model = $this->getManager()->find($this->model, $id);

        if (!($model instanceof $this->model)) {
            return ['success' => false, 'error' => 'The passed id parameter exists no more.'];
        }

        $this->getManager()->remove($model);
        $this->getManager()->flush();

        return ['success' => true];
    }

    /**
     * Internal function which called from the {@link #reloadAssociationAction}.
     * This function contains the logic to reload an association listing for @ORM\OneToMany()
     * associations.
     *
     * The passed id is the primary key value of the configured main model in the {@link #model}
     * property.
     * The passed associationKey contains the property name of the association.
     *
     * Important: This function works only for associations of the configured {@link #model} property.
     * If you want to reload association listings of other models, you have to override this function.
     *
     * @param int    $id
     * @param string $associationKey
     * @param int    $offset
     * @param int    $limit
     * @param array  $sort
     * @param array  $filter
     *
     * @return array
     */
    public function reloadAssociation($id, $associationKey, $offset, $limit, $sort = [], $filter = [])
    {
        $association = $this->getOwningSideAssociation(
            $this->model,
            $associationKey
        );

        $builder = $this->getReloadAssociationQuery(
            $association['sourceEntity'],
            $association['inversedBy'],
            $association['fieldName']
        );

        $sort = $this->getSortConditions(
            $sort,
            $association['sourceEntity'],
            $association['inversedBy']
        );

        $filter = $this->getFilterConditions(
            $filter,
            $association['sourceEntity'],
            $association['inversedBy']
        );

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }
        if (!empty($sort)) {
            $builder->addOrderBy($sort);
        }

        $expr = $this->getManager()->getExpressionBuilder();
        $builder->andWhere(
            $expr->orX(
                $expr->eq($association['fieldName'] . '.id', ':id')
            )
        );
        $builder->setParameter('id', $id);

        $builder->setFirstResult($offset)
            ->setMaxResults($limit);

        $paginator = $this->getQueryPaginator($builder);

        $data = $paginator->getIterator()->getArrayCopy();

        return [
            'success' => true,
            'data' => $data,
            'total' => $paginator->count(),
        ];
    }

    /**
     * This function is used from the {@link #searchAssociationAction} function
     * and is used to find associated data of the configured model like @ORM\ManyToMany() or @ORM\ManyToOne() associations.
     *
     * The function expects the following parameter:
     *  query - Search string which inserted in the search field.
     *  association - Doctrine property name of the association
     *  start - Pagination start value
     *  limit - Pagination limit value
     *
     * @param string   $search
     * @param string   $association
     * @param int      $offset
     * @param int      $limit
     * @param int|null $id
     * @param array    $filter
     * @param array    $sort
     *
     * @return array
     */
    public function searchAssociation($search, $association, $offset, $limit, $id = null, $filter = [], $sort = [])
    {
        $associationModel = $this->getAssociatedModelByProperty($this->model, $association);

        $builder = $this->getSearchAssociationQuery(
            $association,
            $associationModel,
            $search
        );

        $filter = $this->getFilterConditions(
            $filter,
            $associationModel,
            $association
        );

        $sort = $this->getSortConditions(
            $sort,
            $associationModel,
            $association
        );

        if (!empty($filter) && $id === null) {
            $builder->addFilter($filter);
        }

        if (!empty($sort) && $id === null) {
            $builder->addOrderBy($sort);
        }

        $builder->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($id !== null) {
            $this->addIdentifierCondition($association, $id, $builder);
        }

        $paginator = $this->getQueryPaginator($builder);
        $data = $paginator->getIterator()->getArrayCopy();

        return [
            'success' => true,
            'data' => $data,
            'total' => $paginator->count(),
        ];
    }

    /**
     * Helper function to get the repository of the configured model.
     *
     * @return \Shopware\Components\Model\ModelRepository
     */
    protected function getRepository()
    {
        if ($this->repository === null) {
            $this->repository = $this->getManager()->getRepository($this->model);
        }

        return $this->repository;
    }

    /**
     * The getList function returns an array of the configured class model.
     * The listing query created in the getListQuery function.
     * The pagination of the listing is handled inside this function.
     *
     * @param int|null $offset
     * @param int|null $limit
     * @param array    $sort        Contains an array of Ext JS sort conditions
     * @param array    $filter      Contains an array of Ext JS filters
     * @param array    $wholeParams Contains all passed request parameters
     *
     * @return array
     */
    protected function getList($offset, $limit, $sort = [], $filter = [], array $wholeParams = [])
    {
        $builder = $this->getListQuery();
        $builder->setFirstResult($offset)
            ->setMaxResults($limit);

        $filter = $this->getFilterConditions(
            $filter,
            $this->model,
            $this->alias,
            $this->filterFields
        );

        $sort = $this->getSortConditions(
            $sort,
            $this->model,
            $this->alias,
            $this->sortFields
        );

        if (!empty($sort)) {
            $builder->addOrderBy($sort);
        }

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }

        $paginator = $this->getQueryPaginator($builder);
        $data = $paginator->getIterator()->getArrayCopy();
        $count = $paginator->count();

        return ['success' => true, 'data' => $data, 'total' => $count];
    }

    /**
     * Helper function which creates the listing query builder.
     * If the class property model isn't configured, the init function throws an exception.
     * The listing alias for the from table can be configured over the class property alias.
     *
     * @return QueryBuilder
     */
    protected function getListQuery()
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select([$this->alias])
            ->from($this->model, $this->alias);

        return $builder;
    }

    /**
     * Creates the query builder to selected the detailed model data.
     * Override this function to load all associations.
     * Shopware selects as default only the configured model.
     *
     * If you want to load more detailed information you can override this function.
     * Important: We suggest to select not to much association in one query, because the query
     * result could be to big to select the whole data in one query. You can select and add additional
     * data in the {@link #getAdditionalDetailData} function.
     * This function should be used to select @ORM\OneToOne() associations.
     *
     * @example
     *      protected function getDetailQuery($id)
     *      {
     *          $builder = parent::getDetailQuery($id);
     *          $builder->leftJoin('association', 'alias');
     *          $builder->addSelect('alias');
     *          return $builder;
     *      }
     *
     * @param int $id
     *
     * @return QueryBuilder
     */
    protected function getDetailQuery($id)
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select([$this->alias])
            ->from($this->model, $this->alias)
            ->where($this->alias . '.id = :id')
            ->setParameter('id', $id);

        return $builder;
    }

    /**
     * Creates the query builder object for the {@link #searchAssociation} function.
     * It creates a simple query builder object which contains the selection to the associated
     * model and if the $search parameter contains a search value, the function creates an orWhere
     * condition for each model field with a like operation.
     *
     * @param string $association
     * @param string $model
     * @param string $search
     *
     * @return QueryBuilder
     */
    protected function getSearchAssociationQuery($association, $model, $search)
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select($association);
        $builder->from($model, $association);

        if (strlen($search) > 0) {
            $where = [];

            $fields = $this->getModelFields($model, $association);
            foreach ($fields as $field) {
                $where[] = $field['alias'] . ' LIKE :search';
            }
            $builder->andWhere(implode(' OR ', $where));
            $builder->setParameter('search', '%' . $search . '%');
        }

        return $builder;
    }

    /**
     * Creates the query builder object for the {@link #reloadAssociation} function.
     *
     * @param string $model     - Full model class name which will be selected
     * @param string $alias     - Query alias for the selected model
     * @param string $fieldName - Property name of the foreign key column in the associated model
     *
     * @return QueryBuilder
     */
    protected function getReloadAssociationQuery($model, $alias, $fieldName)
    {
        $builder = $this->getManager()->createQueryBuilder();

        $builder->select([$alias]);
        $builder->from($model, $alias);
        $builder->innerJoin($alias . '.' . $fieldName, $fieldName);

        return $builder;
    }

    /**
     * Helper function which resolves the passed Ext JS data of an model.
     * This function resolves the following associations automatically:
     *
     * @ORM\OneToOne() associations
     *      => Ext JS sends even for @ORM\OneToOne() associations, a multi dimensional array
     *      => array('billing' => array( 0 => array('id' => ...) ))
     *      => The function removes the first level of the array to have to model data directly in the association property.
     *      => array('billing' => array('id' => ...))
     *
     * @ORM\ManyToOne() associations
     *      => @ORM\ManyToOne() requires the related doctrine model in the association key property.
     *      => But Ext JS sends only the foreign key property.
     *      => 'article' => array('id' => 1, ... , 'shopId' => 1, 'shop' => null)
     *      => This function resolves the foreign key, removes the foreign key property from the data array and sets the founded doctrine model into the association property.
     *      => 'article' => array('id' => 1, ... , 'shop' => $this->getManager()->find(Model, $data['shopId']);
     *
     * @ORM\ManyToMany() associations
     *      => @ORM\ManyToMany() requires like the @ORM\ManyToOne() associations the resolved doctrine models in the association property.
     *      => But Ext JS sends only an array of foreign keys.
     *      => 'article' => array('id' => 1, 'categories' => array(array('id'=>1), array('id'=>2), ...)
     *      => This function iterates the association property and resolves each foreign key value with the corresponding doctrine model
     *      => 'article' => array('id' => 1, 'categories' => array($this->getManager()->find(Model, 1), $this->getManager()->find(Model, 2), ...)
     *
     * @param array $data
     */
    protected function resolveExtJsData($data)
    {
        $metaData = $this->getManager()->getClassMetadata($this->model);

        foreach ($metaData->getAssociationMappings() as $mapping) {
            /*
             * @ORM\OneToOne associations
             *
             * Ext JS sends even for one to one associations multi dimensional array with association data.
             * @example:
             *    model:            Shopware\Models\Customer\Customer
             *    association:      $billing  (Shopware\Models\Customer\Billing)
             *    required data:    array(
             *                          'id' => 1,
             *                          'billing' => array(
             *                              'street' => '...',
             *                              ...
             *                          )
             *                      )
             *
             *    Ext JS data:      array(
             *                          'id' => 1,
             *                          'billing' => array(
             *                              0 => array(
             *                                  'street' => '...',
             *                                  ...
             *                              )
             *                          )
             *                      )
             *
             * So we have to remove the first level of the posted data.
             */
            if ($mapping['type'] === ClassMetadataInfo::ONE_TO_ONE) {
                $mappingData = $data[$mapping['fieldName']];
                if (array_key_exists(0, $mappingData)) {
                    $data[$mapping['fieldName']] = $data[$mapping['fieldName']][0];
                }
            }

            if (!$mapping['isOwningSide']) {
                continue;
            }

            if ($mapping['type'] === ClassMetadataInfo::MANY_TO_ONE) {
                /**
                 * @ORM\ManyToOne associations.
                 *
                 * The many to one associations requires that the associated model
                 * will be set in the data array.
                 * To resolve the data we have to find out which column are used for
                 * the mapping. This column is defined in the joinColumns array.
                 * To get the passed id, we need to find out the property name of the column.
                 * After getting the property name of the join column,
                 * we can use the entity manager to find the target entity.
                 */
                $column = $mapping['joinColumns'][0]['name'];
                $field = $metaData->getFieldForColumn($column);

                if ($data[$field]) {
                    $associationModel = $this->getManager()->find($mapping['targetEntity'], $data[$field]);

                    //proxies need to be loaded, otherwise the validation will be failed.
                    if ($associationModel instanceof \Doctrine\Common\Persistence\Proxy && method_exists($associationModel, '__load')) {
                        $associationModel->__load();
                    }
                    $data[$mapping['fieldName']] = $associationModel;

                    //remove the foreign key data.
                    unset($data[$field]);
                }
            } elseif ($mapping['type'] === ClassMetadataInfo::MANY_TO_MANY) {
                /**
                 * @ORM\ManyToMany associations.
                 *
                 * The data of many to many association are contained in the corresponding field:
                 *
                 * @example
                 *    model:        Shopware\Models\Article\Article
                 *    association:  $categories  (mapping table: s_articles_categories)
                 *    joined:       - s_articles.id <=> s_articles_categories.articleID
                 *                  - s_categories.id <=> s_articles_categories.categoryID
                 *
                 *    passed data:  'categories' => array(
                 *                      array('id'=>1, ...),
                 *                      array('id'=>2, ...),
                 *                      array('id'=>3, ...)
                 *                  )
                 */
                $associationData = $data[$mapping['fieldName']];
                $associationModels = [];
                foreach ($associationData as $singleData) {
                    $associationModel = $this->getManager()->find($mapping['targetEntity'], $singleData['id']);
                    if ($associationModel) {
                        $associationModels[] = $associationModel;
                    }
                }
                $data[$mapping['fieldName']] = $associationModels;
            }
        }

        return $data;
    }

    /**
     * Helper function which can be used to add additional data which selected over
     * additional queries.
     *
     * @example
     *  You have an @ORM\ManyToMany() association in your doctrine model and won't select
     *  this data over the detail query builder, because the result set would be to big
     *  for a single select.
     *  So you can override this function and add the additional data into the passed data array:
     *
     *      protected function getAdditionalDetailData(array $data)
     *      {
     *          $builder = $this->getManager()->createQueryBuilder();
     *          $builder->select(...)
     *                  ->from(...)
     *
     *          $data['associationName'] = $builder->getQuery()->getArrayResult();
     *
     *          return $data;
     *      }
     *
     * @return array
     */
    protected function getAdditionalDetailData(array $data)
    {
        return $data;
    }

    /**
     * Helper function which return the model name of an association for
     * the passed model and property name.
     *
     * @param string $model
     * @param string $property
     *
     * @return string
     */
    protected function getAssociatedModelByProperty($model, $property)
    {
        $metaData = $this->getManager()->getClassMetadata($model);

        return $metaData->getAssociationTargetClass($property);
    }

    /**
     * Helper function which returns the owning side association definition
     * of the passed property.
     * This function is used to reload association listings over the {@link #reloadAssociation}
     * function.
     *
     * @param string $model
     * @param string $property
     *
     * @return array
     */
    protected function getOwningSideAssociation($model, $property)
    {
        $metaData = $this->getManager()->getClassMetadata($model);
        $mapping = $metaData->getAssociationMapping($property);

        if ($mapping['isOwningSide']) {
            return $mapping;
        }

        $associationMetaData = $this->getManager()->getClassMetadata($mapping['targetEntity']);

        return $associationMetaData->getAssociationMapping($mapping['mappedBy']);
    }

    /**
     * Helper function which adds the listing sort conditions to the passed query builder object.
     *
     * @example
     * The backend listing store of shopware creates a following sort array:
     *  $sort = array(
     *      array('property' => 'name', 'direction' => 'DESC'),
     *      array('property' => 'id', 'direction' => 'ASC')
     *  );
     *
     * Important: Doctrine requires the query builder field alias for each field.
     * You can get a field mapping over the {@link #getModelFields} function.
     * This function creates an associated array with the model field name as array key
     * and as value an array with the query builder field alias under $field['alias'].
     *
     * Shopware resolves the passed Ext JS name over this function and use the alias of the field
     * to sort the query builder.
     *
     * @param array  $sort
     * @param string $model
     * @param string $alias
     * @param array  $whiteList
     *
     * @return array
     */
    protected function getSortConditions($sort, $model, $alias, $whiteList = [])
    {
        $fields = $this->getModelFields($model, $alias);
        $conditions = [];
        foreach ($sort as $condition) {
            //check if the passed field is a valid doctrine model field of the configured model.
            if (!array_key_exists($condition['property'], $fields)) {
                continue;
            }

            //check if the developer limited the sortable fields and the passed property defined in the sort fields parameter.
            if (!empty($whiteList) && !in_array($condition['property'], $whiteList)) {
                continue;
            }
            $condition['property'] = $fields[$condition['property']]['alias'];
            $conditions[] = $condition;
        }

        return $conditions;
    }

    /**
     * This function converts the Ext JS passed filter conditions to
     * a valid doctrine filter array.
     *
     * Ext JS passes only the field name as property name. The doctrine query builder
     * requires additional the table alias for the order by condition field.
     * This function maps the passed Ext JS field with the corresponding model field.
     *
     * The passed whiteList parameter can contains a list of field names which allows to filtered.
     * If the parameter contains an empty array, the function filters each model field.
     *
     * To handle the filter condition by yourself, you can override this function and return
     * the query builder object.
     *
     * @example
     *  Ext JS sends the following filter array:
     *  $filter = array(
     *      array(
     *          'property' => 'name',
     *          'value' => 'Test article',
     *          'operator' => 'OR',
     *          'expression' => 'LIKE'
     *      ),
     *      array(
     *          'property' => 'active',
     *          'value' => '1',
     *          'operator' => 'AND',
     *          'expression' => '='
     *      ),
     *  )
     *
     * @param array  $filters   - List of filter conditions in Ext JS format
     * @param string $model     - Full name of the selected model
     * @param string $alias     - Query alias of the FROM query path
     * @param array  $whiteList - Array of filterable fields, or an empty array
     *
     * @return array
     */
    protected function getFilterConditions($filters, $model, $alias, $whiteList = [])
    {
        $fields = $this->getModelFields($model, $alias);
        $conditions = [];

        foreach ($filters as $condition) {
            if ($condition['property'] === 'search') {
                foreach ($fields as $name => $field) {
                    //check if the developer limited the filterable fields and the passed property defined in the filter fields parameter.
                    if (!empty($whiteList) && !in_array($name, $whiteList)) {
                        continue;
                    }

                    $value = $this->formatSearchValue($condition['value'], $field);

                    $conditions[] = [
                        'property' => $field['alias'],
                        'operator' => 'OR',
                        'value' => $value,
                    ];
                }
            } elseif (array_key_exists($condition['property'], $fields)) {
                //check if the developer limited the filterable fields and the passed property defined in the filter fields parameter.
                if (!empty($whiteList) && !in_array($condition['property'], $whiteList)) {
                    continue;
                }

                $field = $fields[$condition['property']];
                $value = $this->formatSearchValue($condition['value'], $field, $condition['expression']);

                $conditions[] = [
                    'property' => $field['alias'],
                    'operator' => $condition['operator'] ?: null,
                    'value' => $value,
                    'expression' => $condition['expression'],
                ];
            }
        }

        return $conditions;
    }

    /**
     * Helper function to create the query builder paginator.
     *
     * @param Doctrine\ORM\QueryBuilder $builder
     * @param int                       $hydrationMode
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    protected function getQueryPaginator(
        \Doctrine\ORM\QueryBuilder $builder,
        $hydrationMode = \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY
    ) {
        $query = $builder->getQuery();
        $query->setHydrationMode($hydrationMode);

        return $this->getManager()->createPaginator($query);
    }

    /**
     * Helper function which formats the Ext JS search value
     * to a valid doctrine field value for the passed field.
     * This function is used to supports different date search strings
     * like the german and english date format.
     * Additionally this function adds the sql wildcards at the right points of
     * the search value.
     *
     * @param string      $value
     * @param string|null $expression
     *
     * @return string
     */
    protected function formatSearchValue($value, array $field, $expression = null)
    {
        switch ($field['type']) {
            case 'boolean':
                break;
            case 'date':
            case 'datetime':
                //validates the date value. If the value is no date value, return
                $date = date_parse($value);
                if (!checkdate($date['month'], $date['day'], $date['year'])) {
                    $value = '%' . $value . '%';
                    break;
                }

                $date = new DateTime($value);
                $value = $date->format('Y-m-d');
                if (!$this->isSearchExpression($expression)) {
                    return $value;
                }

                //search values for date time should added the % wildcards to search for time values.
                if ($field['type'] === 'datetime') {
                    $value = '%' . $value . '%';
                }
                break;
            case 'integer':
            case 'float':
                break;
            case 'string':
            case 'text':
            default:
                if (!$this->isSearchExpression($expression)) {
                    return $value;
                }
                $value = '%' . $value . '%';
        }

        return $value;
    }

    /**
     * Helper function which returns all field names of the passed model.
     * The alias parameter can be used to prefix the model fields with an query alias.
     * This is required if you select more than one table over an doctrine query builder.
     *
     * The returned array is associated with the model field names.
     *
     * @param string      $model - Model class name
     * @param string|null $alias - Allows to add an query alias like 'article.name'.
     *
     * @return array
     */
    protected function getModelFields($model, $alias = null)
    {
        $metaData = $this->getManager()->getClassMetadata($model);
        $fields = $metaData->getFieldNames();
        $fields = array_combine($fields, $fields);

        if ($alias) {
            foreach ($fields as &$field) {
                $field = [
                    'alias' => $alias . '.' . $field,
                    'type' => $metaData->getTypeOfField($field),
                ];
            }
        }

        return $fields;
    }

    /**
     * @param string|null $expression
     *
     * @return bool
     */
    private function isSearchExpression($expression)
    {
        return $expression === 'LIKE' || $expression === null;
    }

    /**
     * Returns the reference column for the provided association property
     *
     * @param string $association
     *
     * @return string|null
     */
    private function getReferencedColumnName($association)
    {
        $metaData = Shopware()->Models()->getClassMetadata($this->model);
        $mappings = $metaData->getAssociationMappings();

        if (!isset($mappings[$association])) {
            return null;
        }

        $mapping = $mappings[$association];
        $column = array_shift($mapping['joinColumns']);
        $column = $column['referencedColumnName'];

        return $column;
    }

    /**
     * Filters the search association query by the identifier field.
     * Used for form loading if the raw value is set to the value.
     *
     * @param string $association
     * @param int    $id
     */
    private function addIdentifierCondition($association, $id, QueryBuilder $builder)
    {
        $column = $this->getReferencedColumnName($association);

        if (!isset($column)) {
            return;
        }

        $builder->where($association . '.' . $column . ' = :id')
            ->setParameter('id', $id)
            ->setFirstResult(0)
            ->setMaxResults(1);
    }
}
