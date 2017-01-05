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
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
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
     * @param Connection $connection
     * @param \Enlight_Event_EventManager $eventManager
     * @param ConditionHandlerInterface[] $conditionHandlers
     * @param SortingHandlerInterface[] $sortingHandlers
     */
    public function __construct(
        Connection $connection,
        \Enlight_Event_EventManager $eventManager,
        $conditionHandlers = [],
        $sortingHandlers = []
    ) {
        $this->connection = $connection;
        $this->conditionHandlers = $conditionHandlers;
        $this->sortingHandlers = $sortingHandlers;
        $this->eventManager = $eventManager;

        $this->conditionHandlers = $this->registerConditionHandlers();
        $this->sortingHandlers = $this->registerSortingHandlers();
    }

    /**
     * @inheritdoc
     */
    public function createQueryWithSorting(Criteria $criteria, ShopContextInterface $context)
    {
        $query = $this->createQuery($criteria, $context);

        $this->addSorting($criteria, $query, $context);

        return $query;
    }

    /**
     * @inheritdoc
     */
    public function createProductQuery(Criteria $criteria, ShopContextInterface $context)
    {
        $query = $this->createQueryWithSorting($criteria, $context);

        $select = $query->getQueryPart('select');

        if ($criteria->fetchCount()) {
            $query->select([
                'SQL_CALC_FOUND_ROWS product.id as __product_id',
                'variant.id                     as __variant_id',
                'variant.ordernumber            as __variant_ordernumber'
            ]);
        } else {
            $query->select([
                'product.id as __product_id',
                'variant.id                     as __variant_id',
                'variant.ordernumber            as __variant_ordernumber'
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
     * @inheritdoc
     */
    public function createQuery(Criteria $criteria, ShopContextInterface $context)
    {
        $query = $this->createQueryBuilder();

        $query->from('s_articles', 'product')
            ->innerJoin(
                'product',
                's_articles_details',
                'variant',
                'variant.id = product.main_detail_id
                 AND variant.active = 1
                 AND product.active = 1'
            )
            ->innerJoin(
                'product',
                's_core_tax',
                'tax',
                'tax.id = product.taxID'
            )
            ->innerJoin(
                'variant',
                's_articles_attributes',
                'productAttribute',
                'productAttribute.articledetailsID = variant.id'
            );

        $this->addConditions($criteria, $query, $context);

        return $query;
    }

    /**
     * @inheritdoc
     */
    public function createQueryBuilder()
    {
        return new QueryBuilder($this->connection);
    }

    /**
     * @param Criteria $criteria
     * @param QueryBuilder $query
     * @param ShopContextInterface $context
     */
    private function addConditions(Criteria $criteria, QueryBuilder $query, ShopContextInterface $context)
    {
        foreach ($criteria->getConditions() as $condition) {
            $handler = $this->getConditionHandler($condition);
            $handler->generateCondition($condition, $query, $context);
        }
    }

    /**
     * @param Criteria $criteria
     * @param QueryBuilder $query
     * @param ShopContextInterface $context
     * @throws \Exception
     */
    private function addSorting(Criteria $criteria, QueryBuilder $query, ShopContextInterface $context)
    {
        foreach ($criteria->getSortings() as $sorting) {
            $handler = $this->getSortingHandler($sorting);
            $handler->generateSorting($sorting, $query, $context);
        }
    }

    /**
     * @param SortingInterface $sorting
     * @throws \Exception
     * @return SortingHandlerInterface
     */
    private function getSortingHandler(SortingInterface $sorting)
    {
        foreach ($this->sortingHandlers as $handler) {
            if ($handler->supportsSorting($sorting)) {
                return $handler;
            }
        }

        throw new \Exception(sprintf("Sorting %s not supported", get_class($sorting)));
    }

    /**
     * @param ConditionInterface $condition
     * @throws \Exception
     * @return ConditionHandlerInterface
     */
    private function getConditionHandler(ConditionInterface $condition)
    {
        foreach ($this->conditionHandlers as $handler) {
            if ($handler->supportsCondition($condition)) {
                return $handler;
            }
        }

        throw new \Exception(sprintf("Condition %s not supported", get_class($condition)));
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
     * @param ArrayCollection $objects
     * @param string $class
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
}
