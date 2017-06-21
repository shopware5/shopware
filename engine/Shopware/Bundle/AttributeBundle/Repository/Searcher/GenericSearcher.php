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

namespace Shopware\Bundle\AttributeBundle\Repository\Searcher;

use Doctrine\ORM\AbstractQuery;
use Shopware\Bundle\AttributeBundle\Repository\SearchCriteria;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\QueryBuilder;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class GenericSearcher implements SearcherInterface
{
    /**
     * @var string
     */
    protected $entity;

    /**
     * @var ModelManager
     */
    protected $entityManager;

    /**
     * GenericSearcher constructor.
     *
     * @param string       $entity
     * @param ModelManager $entityManager
     */
    public function __construct($entity, ModelManager $entityManager)
    {
        $this->entity = $entity;
        $this->entityManager = $entityManager;
    }

    /**
     * @param SearchCriteria $criteria
     *
     * @return SearcherResult
     */
    public function search(SearchCriteria $criteria)
    {
        $builder = $this->createQuery($criteria);

        $builder->setFirstResult($criteria->offset)
            ->setMaxResults($criteria->limit);

        if ($criteria->term) {
            $this->addSearchTermCondition($criteria, $builder);
        }
        if ($criteria->conditions) {
            $builder->addFilter($criteria->conditions);
        }
        if ($criteria->sortings) {
            $builder->addOrderBy($criteria->sortings);
        }

        return $this->createResult($builder);
    }

    /**
     * @param SearchCriteria $criteria
     *
     * @return \Doctrine\ORM\QueryBuilder|QueryBuilder
     */
    protected function createQuery(SearchCriteria $criteria)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select($this->getIdentifierField());
        $builder->from($criteria->entity, 'entity');
        $builder->setAlias('entity');

        return $builder;
    }

    /**
     * @param SearchCriteria $criteria
     * @param QueryBuilder   $builder
     */
    protected function addSearchTermCondition(SearchCriteria $criteria, QueryBuilder $builder)
    {
        $fields = $this->getSearchFields($criteria);
        $search = [];
        foreach ($fields as $field) {
            $search[] = $field . ' LIKE :search';
        }
        $builder->andWhere(implode(' OR ', $search));
        $builder->setParameter(':search', '%' . $criteria->term . '%');
    }

    /**
     * @param SearchCriteria $criteria
     *
     * @return string[]
     */
    protected function getSearchFields(SearchCriteria $criteria)
    {
        $classMetaData = $this->entityManager->getClassMetadata($this->entity);

        return array_map(function ($field) {
            return 'entity.' . $field;
        }, $classMetaData->fieldNames);
    }

    /**
     * @param QueryBuilder $builder
     *
     * @return SearcherResult
     */
    protected function createResult(QueryBuilder $builder)
    {
        $query = $builder->getQuery();
        $query->setHydrationMode(AbstractQuery::HYDRATE_ARRAY);
        $paginator = $this->entityManager->createPaginator($query);
        $result = $paginator->getIterator()->getArrayCopy();

        $field = $this->getIdentifierField();
        $field = explode('.', $field);

        return new SearcherResult(
            array_column($result, $field[1]),
            $paginator->count()
        );
    }

    /**
     * @return string
     */
    protected function getIdentifierField()
    {
        return 'entity.id';
    }
}
