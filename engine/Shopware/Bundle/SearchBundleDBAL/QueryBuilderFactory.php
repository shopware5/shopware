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

namespace Shopware\Bundle\SearchBundleDBAL;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use IteratorAggregate;
use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class QueryBuilderFactory implements QueryBuilderFactoryInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var SortingHandlerInterface[]
     */
    private $sortingHandlers;

    /**
     * @var ConditionHandlerInterface[]
     */
    private $conditionHandlers;

    /**
     * @throws \RuntimeException
     * @throws \Enlight_Event_Exception
     */
    public function __construct(
        Connection $connection,
        \Enlight_Event_EventManager $eventManager,
        IteratorAggregate $conditionHandlers,
        IteratorAggregate $sortingHandlers,
        ContainerInterface $container
    ) {
        $this->connection = $connection;
        $this->conditionHandlers = iterator_to_array($conditionHandlers, false);
        $this->sortingHandlers = iterator_to_array($sortingHandlers, false);
        $this->eventManager = $eventManager;

        $this->conditionHandlers = $this->registerConditionHandlers();
        $this->sortingHandlers = $this->registerSortingHandlers();

        $container->set('shopware_searchdbal.condition_handlers', $this->conditionHandlers);
        $container->set('shopware_searchdbal.sorting_handlers', $this->sortingHandlers);
    }

    /**
     * {@inheritdoc}
     */
    public function createQueryWithSorting(Criteria $criteria, ShopContextInterface $context)
    {
        $query = $this->createQuery($criteria, $context);

        $this->addSorting($criteria, $query, $context);

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function createProductQuery(Criteria $criteria, ShopContextInterface $context)
    {
        $query = $this->createQueryWithSorting($criteria, $context);

        $select = $query->getQueryPart('select');

        if ($criteria->fetchCount()) {
            $query->select([
                'SQL_CALC_FOUND_ROWS product.id as __product_id',
                'variant.id                     as __variant_id',
                'variant.ordernumber            as __variant_ordernumber',
            ]);
        } else {
            $query->select([
                'product.id as __product_id',
                'variant.id                     as __variant_id',
                'variant.ordernumber            as __variant_ordernumber',
            ]);
        }

        foreach ($select as $selection) {
            $query->addSelect($selection);
        }

        $query->addGroupBy('product.id');

        if ($criteria->getOffset()) {
            $query->setFirstResult($criteria->getOffset());
        }
        if ($criteria->getLimit()) {
            $query->setMaxResults($criteria->getLimit());
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function createQuery(Criteria $criteria, ShopContextInterface $context)
    {
        $query = $this->createQueryBuilder();

        $this->prepareHandlers($criteria);

        $query->from('s_articles', 'product');

        if ($criteria->hasConditionOfClass(VariantCondition::class)) {
            $query->innerJoin(
                'product',
                's_articles_details',
                'variant',
                'variant.articleID = product.id
                 AND variant.active = 1
                 AND product.active = 1'
            );
        } else {
            $query->innerJoin(
                'product',
                's_articles_details',
                'variant',
                'variant.id = product.main_detail_id
                 AND variant.active = 1
                 AND product.active = 1'
            );
        }
        $query->innerJoin(
            'variant',
            's_articles_attributes',
            'productAttribute',
            'productAttribute.articledetailsID = variant.id'
        );

        $this->addConditions($criteria, $query, $context);

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function createQueryBuilder()
    {
        return new QueryBuilder($this->connection);
    }

    private function addConditions(Criteria $criteria, QueryBuilder $query, ShopContextInterface $context)
    {
        foreach ($criteria->getConditions() as $condition) {
            $handler = $this->getConditionHandler($condition);
            $handler->generateCondition($condition, $query, $context);
        }
    }

    private function addSorting(Criteria $criteria, QueryBuilder $query, ShopContextInterface $context)
    {
        foreach ($criteria->getSortings() as $sorting) {
            $handler = $this->getSortingHandler($sorting);
            $handler->generateSorting($sorting, $query, $context);
        }
        $query->addOrderBy('variant.id', 'ASC');
    }

    /**
     * @throws \Exception
     *
     * @return SortingHandlerInterface
     */
    private function getSortingHandler(SortingInterface $sorting)
    {
        foreach ($this->sortingHandlers as $handler) {
            if ($handler->supportsSorting($sorting)) {
                return $handler;
            }
        }

        throw new \Exception(sprintf('Sorting %s not supported', get_class($sorting)));
    }

    /**
     * @throws \Exception
     *
     * @return ConditionHandlerInterface
     */
    private function getConditionHandler(ConditionInterface $condition)
    {
        foreach ($this->conditionHandlers as $handler) {
            if ($handler->supportsCondition($condition)) {
                return $handler;
            }
        }

        throw new \Exception(sprintf('Condition %s not supported', get_class($condition)));
    }

    /**
     * @return SortingHandlerInterface[]
     */
    private function registerSortingHandlers()
    {
        $sortingHandlers = new ArrayCollection();
        $sortingHandlers = $this->eventManager->collect(
            'Shopware_SearchBundleDBAL_Collect_Sorting_Handlers',
            $sortingHandlers
        );

        $this->assertCollectionIsInstanceOf($sortingHandlers, __NAMESPACE__ . '\SortingHandlerInterface');

        return array_merge($sortingHandlers->toArray(), $this->sortingHandlers);
    }

    /**
     * @return ConditionHandlerInterface[]
     */
    private function registerConditionHandlers()
    {
        $conditionHandlers = new ArrayCollection();
        $conditionHandlers = $this->eventManager->collect(
            'Shopware_SearchBundleDBAL_Collect_Condition_Handlers',
            $conditionHandlers
        );

        $this->assertCollectionIsInstanceOf($conditionHandlers, __NAMESPACE__ . '\ConditionHandlerInterface');

        return array_merge($conditionHandlers->toArray(), $this->conditionHandlers);
    }

    /**
     * @param string $class
     *
     * @throws \RuntimeException
     */
    private function assertCollectionIsInstanceOf(ArrayCollection $objects, $class)
    {
        foreach ($objects as $object) {
            if (!$object instanceof $class) {
                throw new \RuntimeException(
                    sprintf(
                        'Object of class "%s" must be instance of "%s".',
                        get_class($object),
                        $class
                    )
                );
            }
        }
    }

    /**
     * @param Criteria $criteria
     */
    private function prepareHandlers($criteria)
    {
        $handlers = array_merge(
            $this->conditionHandlers,
            $this->sortingHandlers
        );

        foreach ($handlers as $handler) {
            if ($handler instanceof CriteriaAwareInterface) {
                $handler->setCriteria($criteria);
            }
        }
    }
}
