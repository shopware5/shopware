<?php

use Doctrine\ORM\Tools\Pagination\Paginator;

class Shopware_Controllers_Backend_Application extends Shopware_Controllers_Backend_ExtJs
{
    protected $model = null;
    protected $alias = 'modelAlias';

    protected $filterFields = null;
    protected $sortFields = null;
    protected $associations = array();

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

    protected function getList($offset, $limit, $sort = array(), $filter = array())
    {
        $builder = $this->getListQuery();
        $builder->setFirstResult($offset)
            ->setMaxResults($limit);

        $query = $builder->getQuery();
        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $paginator = new Paginator($query);

        $data = $paginator->getIterator()->getArrayCopy();
        $count = $paginator->count();
        return array('success' => true, 'data' => $data, 'total' => $count);
    }

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

    protected function save(array $data)
    {

        $detail = $this->getDetail($data['id']);
        return array('success' => true, 'data' => $detail['data']);
    }

    public function deleteAction()
    {
        $this->View()->assign(
            $this->delete(
                $this->Request()->getParam('id', array())
            )
        );
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
     * @param $association string
     * @return array
     */
    protected function searchAssociation($search, $association)
    {
        $model = $this->associations[$association];

        $builder = $this->getSearchAssociationQuery($association, $model, $search);
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
        $builder->setParameter('search', '%'. $search .'%');
        $builder->setFirstResult(0)
                ->setMaxResults(20);

        return $builder;
    }

}