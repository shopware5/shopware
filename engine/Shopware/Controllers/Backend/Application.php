<?php

use Doctrine\ORM\Tools\Pagination\Paginator;

class Shopware_Controllers_Backend_Application extends Shopware_Controllers_Backend_ExtJs
{
    protected $model = null;
    protected $alias = 'modelAlias';

    protected $filterFields = null;
    protected $sortFields = null;

    /**
     * Controller action which can be called over an ajax request.
     * This function is normally used for backend listings.
     *
     * @internalParam start  - Offset for the pagination
     * @internalParam limit  - Integer value for the max row count
     * @internalParam sort   - Contains an array with sort conditions
     * @internalParam filter - Contains an array with filter conditions
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
        foreach($sort as $condition) {
            if (!array_key_exists($condition['property'], $fields)) {
                continue;
            }
            $condition['property'] = $fields[$condition['property']];
            $conditions[] = $condition;
        }

        if (!empty($conditions)) {
            $builder->addOrderBy($conditions);
        }

        return $builder;
    }

    /**
     * @param \Shopware\Components\Model\QueryBuilder $builder
     * @param array $filters
     * @return \Shopware\Components\Model\QueryBuilder
     */
    protected function addListingFilterCondition(\Shopware\Components\Model\QueryBuilder $builder, array $filters)
    {
        $fields = $this->getModelFields($this->model, $this->alias);
        $conditions = array();

        foreach($filters as $condition) {
            if ($condition['property'] === 'search') {
                foreach($fields as $field) {
                    $conditions[] = array(
                        'property' => $field,
                        'operator' => 'OR',
                        'value' => '%' . $condition['value'] . '%'
                    );
                }
            } elseif (array_key_exists($condition['property'], $fields)) {
                $conditions[] = array(
                    'property' => $fields[$condition['property']],
                    'operator' => 'OR',
                    'value' => '%' . $condition['value'] . '%'
                );
            }
        }

        if (!empty($conditions)) {
            $builder->addFilter($conditions);
        }

        return $builder;
    }


    /**
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
            $fields = array_map(function($field) use ($alias){
                return $alias . '.' . $field;
            }, $fields);
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
    protected function getQueryPaginator(\Doctrine\ORM\QueryBuilder $builder, $hydrationMode = \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY)
    {
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
                $this->Request()->getParam('id', array())
            )
        );
    }


    protected function save(array $data)
    {
        $detail = $this->getDetail($data['id']);
        return array('success' => true, 'data' => $detail['data']);
    }

    protected function delete(array $ids)
    {
        return array('success' => true);
    }


    public function searchAssociationAction()
    {
        $this->View()->assign(
            $this->searchAssociation(
                $this->Request()->getParam('query', null),
                $this->Request()->getParam('association', null)
            )
        );
    }

    /**
     * @param $search
     * @param $association string
     * @return array
     */
    protected function searchAssociation($search, $association)
    {
        $builder = $this->getSearchAssociationQuery(
            $association,
            $this->getAssociatedModelByProperty($this->model, $association),
            $search
        );

        $query = $builder->getQuery();

        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $paginator = new Paginator($query);
        $data = $paginator->getIterator()->getArrayCopy();

        return array(
            'success' => true,
            'data' => $data,
            'count' => $paginator->getIterator()->count()
        );
    }

    protected function getSearchAssociationQuery($association, $model, $search)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select($association);
        $builder->from($model, $association);
        $builder->where($association . '.name LIKE :search');
        $builder->setParameter('search', '%' . $search . '%');
        $builder->setFirstResult(0)
            ->setMaxResults(20);

        return $builder;
    }


    protected function getAssociatedModelByProperty($model, $property)
    {
        $metaData = Shopware()->Models()->getClassMetadata($model);
        return $metaData->getAssociationTargetClass($property);
    }


}