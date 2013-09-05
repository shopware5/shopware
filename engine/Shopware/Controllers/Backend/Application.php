<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Base controller for a single backend sub application.
 * This controller contains many functions for the quad operations for a single model
 * The Shopware_Controllers_Backend_Application can only be used if the application works
 * with the Shopware 4 Doctrine models.
 * Otherwise the controller functions throws different exception that the model has to be configured.
 * In this case use the Shopware_Controllers_Backend_ExtJs controller for your backend application.
 *
 * How to use:
 *  - Create a new backend controller with the name of your backend application
 *      Example:
 *      - Your backend application is called "Shopware.apps.Product"
 *      - So create a new backend php controller "Shopware_Controllers_Backend_Product"
 *  - The only think you have to do now, is to configure the doctrine model in the $model class property.
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
 *
 * @category  Shopware
 * @package   Shopware\Controllers\Backend
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_Application extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * The model property is the only required class property.
     * If this property isn't set, the whole backend application don't works.
     * To configure this property you have only to set the whole model class name into this parameter.
     *
     * @example
     * $model = 'Shopware\Models\Article\Article'
     *
     * @required
     * @var string model
     */
    protected $model;

    /**
     * The model alias is used for the listing and detail query builder.
     * If you want to use an own parameter please override this property.
     * Otherwise you can terminate your FROM table in the listing and detail query
     * by prefix your fields with "modelAlias.name".
     *
     * @var string $alias
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
     * @var array $filterFields
     */
    protected $filterFields;


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
     * @var array $sortFields
     */
    protected $sortFields;

    /**
     * Helper function to get the repository of the configured model.
     * @return \Shopware\Models\Partner\Repository
     */
    protected function getRepository()
    {
        return Shopware()->Models()->getRepository($this->model);
    }

    /**
     * Controller action which can be called over an ajax request.
     * This function is normally used for backend listings.
     * The listing will be selected over the getList function.
     *
     * @requestParam start  - Offset for the pagination
     * @requestParam limit  - Integer value for the max row count
     * @requestParam sort   - Contains an array with sort conditions
     * @requestParam filter - Contains an array with filter conditions
     */
    public function listAction()
    {
        $this->View()->assign(
            $this->getList(
                $this->Request()->getParam('start', 0),
                $this->Request()->getParam('limit', 20),
                $this->Request()->getParam('sort', array()),
                $this->Request()->getParam('filter', array())
            )
        );
    }

    /**
     * The getList function returns an array of the configured class model.
     * The listing query created in the getListQuery function.
     * The pagination of the listing is handled inside this function.
     *
     * @param int $offset
     * @param int $limit
     * @param array $sort
     * @param array $filter
     * @return array
     */
    protected function getList($offset, $limit, $sort = array(), $filter = array())
    {
        $builder = $this->getListQuery();
        $builder->setFirstResult($offset)
                ->setMaxResults($limit);

        $builder = $this->addListingSortCondition($builder, $sort);
        $builder = $this->addListingFilterCondition($builder, $filter);

        $paginator = $this->getQueryPaginator($builder);
        $data = $paginator->getIterator()->getArrayCopy();
        $count = $paginator->count();

        return array('success' => true, 'data' => $data, 'total' => $count);
    }

    /**
     *
     * @param \Shopware\Components\Model\QueryBuilder $builder
     * @param array $sort
     * @return \Shopware\Components\Model\QueryBuilder
     */
    protected function addListingSortCondition(\Shopware\Components\Model\QueryBuilder $builder, array $sort)
    {
        $fields = $this->getModelFields($this->model, $this->alias);
        $conditions = array();
        foreach ($sort as $condition) {
            if (!array_key_exists($condition['property'], $fields)) {
                continue;
            }
            $condition['property'] = $fields[$condition['property']]['alias'];
            $conditions[] = $condition;
        }

        if (!empty($conditions)) {
            $builder->addOrderBy($conditions);
        }

        return $builder;
    }

    /**
     * This function adds the filter conditions for the listing query.
     * Ext JS passes only the field name as property name. The doctrine query builder
     * requires additional the table alias for the order by condition field.
     * This function maps the passed Ext JS field with the corresponding model field.
     *
     * If you only want to shrink the available filter fields, you can configure the available
     * filter fields in the class property $filterFields.
     *
     * To handle the filter condition by yourself, you can override this function and return
     * the query builder object.
     *
     * @param \Shopware\Components\Model\QueryBuilder $builder
     * @param array $filters
     * @return \Shopware\Components\Model\QueryBuilder
     */
    protected function addListingFilterCondition(\Shopware\Components\Model\QueryBuilder $builder, array $filters)
    {
        $fields = $this->getModelFields($this->model, $this->alias);
        $conditions = array();

        foreach ($filters as $condition) {
            if ($condition['property'] === 'search') {
                foreach ($fields as $field) {
                    $value = $this->formatSearchValue($condition['value'], $field);

                    $conditions[] = array(
                        'property' => $field['alias'],
                        'operator' => 'OR',
                        'value' => $value
                    );
                }

            } elseif (array_key_exists($condition['property'], $fields)) {
                $field = $fields[$condition['property']];
                $value = $this->formatSearchValue($condition['value'], $field);

                $conditions[] = array(
                    'property' => $field['alias'],
                    'operator' => $condition['operator'],
                    'value' => $value,
                    'expression' => $condition['expression']
                );
            }
        }
        if (!empty($conditions)) {
            $builder->addFilter($conditions);
        }


        return $builder;
    }

    protected function formatSearchValue($value, $field)
    {
        switch($field['type']) {
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
                //search values for date time should added the % wildcards to search for time values.
                if ($field['datetime']) {
                    $value = '%' . $value . '%';
                }
                break;
            case 'string':
            case 'text':
            default:
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
     * @param string $model - Model class name
     * @param null $alias - Allows to add an query alias like 'article.name'.
     * @return array
     */
    protected function getModelFields($model, $alias = null)
    {
        $metaData = Shopware()->Models()->getClassMetadata($model);
        $fields = $metaData->getFieldNames();
        $fields = array_combine($fields, $fields);

        if ($alias) {
            foreach($fields as &$field) {
                $field = array(
                    'alias' => $alias . '.' . $field,
                    'type' => $metaData->getTypeOfField($field)
                );
            }
        }

        return $fields;
    }


    /**
     * Helper function to create the query builder paginator.
     *
     * @param Doctrine\ORM\QueryBuilder $builder
     * @param int $hydrationMode
     * @return Paginator
     */
    protected function getQueryPaginator(
        \Doctrine\ORM\QueryBuilder $builder,
        $hydrationMode = \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY
    ) {
        $query = $builder->getQuery();
        $query->setHydrationMode($hydrationMode);
        return new Paginator($query);
    }


    /**
     * Helper function which creates the listing query builder.
     * If the class property model isn't configured, the function throws an exception.
     * The listing alias for the from table can be configured over the class property alias.
     *
     * @return \Doctrine\ORM\QueryBuilder|\Shopware\Components\Model\QueryBuilder
     * @throws Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    protected function getListQuery()
    {
        if (empty($this->model)) {
            throw new \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException(
                'The model property of your PHP-Controller is not configured!'
            );
        }
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array($this->alias))
                ->from($this->model, $this->alias);

        return $builder;
    }


    public function detailAction()
    {
        $this->View()->assign(
            $this->getDetail(
                $this->Request()->getParam('id')
            )
        );
    }

    public function getDetail($id)
    {
        $builder = $this->getDetailQuery($id);
        $query = $builder->getQuery();

        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $paginator = new Paginator($query);

        $data = $paginator->getIterator()->current();
        $data = $this->getAdditionalDetailData($data);

        return array('success' => true, 'data' => $data);
    }


    protected function getAdditionalDetailData(array $data)
    {
        return $data;
    }

    /**
     * Creates the query builder to selected the detailed model data.
     * Override this function to load all associations.
     *
     * @param $id
     * @return \Doctrine\ORM\QueryBuilder|\Shopware\Components\Model\QueryBuilder
     * @throws Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    protected function getDetailQuery($id)
    {
        if (empty($this->model)) {
            throw new \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException(
                'The model property of your PHP-Controller is not configured!'
            );
        }
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array($this->alias))
            ->from($this->model, $this->alias)
            ->where($this->alias . '.id = :id')
            ->setParameter('id', $id);

        return $builder;
    }


    public function createAction()
    {
        $this->View()->assign(
            $this->save(
                $this->Request()->getParams()
            )
        );
    }

    public function updateAction()
    {
        $this->View()->assign(
            $this->save(
                $this->Request()->getParams()
            )
        );
    }

    public function deleteAction()
    {
        $this->View()->assign(
            $this->delete(
                $this->Request()->getParam('id', null)
            )
        );
    }


    protected function save($data)
    {
        try {
            /**@var $model \Shopware\Components\Model\ModelEntity */
            $model = new $this->model();
            if (!empty($data['id'])) {
                $model = Shopware()->Models()->find($this->model, $data['id']);
            } else {
                Shopware()->Models()->persist($model);
            }
            $data = $this->resolveExtJsData($data);
            $model->fromArray($data);

            $violations = Shopware()->Models()->validate($model);
            $errors = array();
            /**@var $violation Symfony\Component\Validator\ConstraintViolation */
            foreach ($violations as $violation) {
                $errors[] = array(
                    'message' => $violation->getMessage(),
                    'property' => $violation->getPropertyPath()
                );
            }

            if (!empty($errors)) {
                return array('success' => false, 'violations' => $errors);
            }

            Shopware()->Models()->flush();

            $detail = $this->getDetail($model->getId());

            return array('success' => true, 'data' => $detail['data']);
        } catch (Exception $e) {
            return array('success' => true, 'error' => $e->getMessage());
        }
    }


    protected function resolveExtJsData($data)
    {
        $metaData = Shopware()->Models()->getClassMetadata($this->model);

        foreach($metaData->getAssociationMappings() as $mapping) {
            /**
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
            if ($mapping['type'] === 1) {
                $mappingData = $data[$mapping['fieldName']];
                if (array_key_exists(0, $mappingData)) {
                    $data[$mapping['fieldName']] = $data[$mapping['fieldName']][0];
                }
            }

            if (!$mapping['isOwningSide']) {
                continue;
            }

            if ($mapping['type'] === 2) {
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
                    $associationModel = Shopware()->Models()->find($mapping['targetEntity'], $data[$field]);

                    //proxies need to be loaded, otherwise the validation will be failed.
                    if ($associationModel instanceof \Doctrine\Common\Persistence\Proxy && method_exists($associationModel, '__load')) {
                        $associationModel->__load();
                    }
                    $data[$mapping['fieldName']] = $associationModel;

                    //remove the foreign key data.
                    unset($data[$field]);
                }

            } elseif ($mapping['type'] === 8) {
                /**
                 * @ORM\ManyToMany associations.
                 *
                 * The data of many to many association are contained in the corresponding field:
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
                $associationModels = array();
                foreach($associationData as $singleData) {
                    $associationModel = Shopware()->Models()->find($mapping['targetEntity'], $singleData['id']);
                    if ($associationModel) {
                        $associationModels[] = $associationModel;
                    }
                }
                $data[$mapping['fieldName']] = $associationModels;
            }

        }
        return $data;
    }


    protected function delete($id)
    {
        if (empty($id)) {
            return array('success' => false, 'error' => 'The id parameter contains no value.');
        }

        $model = Shopware()->Models()->find($this->model, $id);

        if (!($model instanceof $this->model)) {
            return array('success' => false, 'error' => 'The passed id parameter exists no more.');
        }

        Shopware()->Models()->remove($model);
        Shopware()->Models()->flush();

        return array('success' => true);
    }


    public function searchAssociationAction()
    {
        $this->View()->assign(
            $this->searchAssociation(
                $this->Request()->getParam('query', null),
                $this->Request()->getParam('association', null),
                $this->Request()->getParam('start', 0),
                $this->Request()->getParam('limit', 20)
            )
        );
    }

    /**
     * @param $search
     * @param $association string
     * @param $offset
     * @param $limit
     * @return array
     */
    protected function searchAssociation($search, $association, $offset, $limit)
    {
        $builder = $this->getSearchAssociationQuery(
            $association,
            $this->getAssociatedModelByProperty($this->model, $association),
            $search
        );

        $builder->setFirstResult($offset)
                ->setMaxResults($limit);

        $query = $builder->getQuery();

        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $paginator = new Paginator($query);
        $data = $paginator->getIterator()->getArrayCopy();

        return array(
            'success' => true,
            'data' => $data,
            'total' => $paginator->count()
        );
    }

    protected function getSearchAssociationQuery($association, $model, $search)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select($association);
        $builder->from($model, $association);

        if (strlen($search) > 0) {
            $fields = $this->getModelFields($model, $association);
            foreach($fields as $field) {
                $builder->orWhere($field['alias'] . ' LIKE :search');
            }
            $builder->setParameter('search', '%' . $search . '%');
        }

        return $builder;
    }


    protected function getAssociatedModelByProperty($model, $property)
    {
        $metaData = Shopware()->Models()->getClassMetadata($model);
        return $metaData->getAssociationTargetClass($property);
    }


}