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

namespace Shopware\Components\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder as BaseQueryBuilder;

/**
 * The Shopware QueryBuilder is an extension of the standard Doctrine QueryBuilder.
 */
class QueryBuilder extends BaseQueryBuilder
{
    /**
     * @var string|null
     */
    protected $alias;

    /**
     * @var QueryOperatorValidator
     */
    protected $operatorValidator;

    public function __construct(EntityManagerInterface $em, QueryOperatorValidator $operatorValidator)
    {
        $this->operatorValidator = $operatorValidator;

        parent::__construct($em);
    }

    /**
     * @param string $alias
     *
     * @return QueryBuilder
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Sets a collection of query parameters for the query being constructed.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u')
     *         ->where('u.id = :user_id1 OR u.id = :user_id2')
     *         ->setParameters(new ArrayCollection(array(
     *             new Parameter('user_id1', 1),
     *             new Parameter('user_id2', 2)
     )));
     * </code>
     *
     * Notice: This method overrides ALL parameters in Doctrine 2.3 and up.
     * We keep the old Doctrine < 2.3 behavior here for Shopware BC reasons,
     * however this will change in the future. Use {@link setParameter()}
     * instead or call {@link setParameters()} only once, or with all the
     * parameters.
     *
     * @deprecated This method is deprecated since 5.4.
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|array $parameters the query parameters to set
     *
     * @return QueryBuilder this QueryBuilder instance
     */
    public function setParameters($parameters)
    {
        trigger_error(sprintf('%s::%s() is deprecated. Please use setParameter().', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $existingParameters = $this->getParameters();

        if (count($existingParameters) && is_array($parameters)) {
            return $this->addParameters($parameters);
        }

        return parent::setParameters($parameters);
    }

    /**
     * Temporary helper method to use instead of {@link setParameters()},
     * when you really want old Doctrine parameter behavior.
     *
     * Warning: This method will be removed in Shopware 5+ and you
     * should only use it to quickly move backwards to the old
     * {@link setParameters()} behavior.
     *
     * @deprecated This method is deprecated since 5.4.
     *
     * @return QueryBuilder this QueryBuilder instance
     */
    public function addParameters(array $parameters)
    {
        trigger_error(sprintf('%s::%s() is deprecated. Please use addParameter().', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $existingParameters = $this->getParameters();
        $newParameters = new ArrayCollection();

        foreach ($existingParameters as $existingParameter) {
            if (!isset($parameters[$existingParameter->getName()])) {
                $newParameters->add($existingParameter);
            }
        }

        foreach ($parameters as $key => $value) {
            $parameter = new \Doctrine\ORM\Query\Parameter($key, $value);
            $newParameters->add($parameter);
        }

        $this->setParameters($newParameters);

        return $this;
    }

    /**
     * Adds filters to the query results.
     *
     * <code>
     *      $this->addFilter(array(
     *          'name' => 'A%'
     *      ));
     * </code>
     *
     * <code>
     *      $this->addFilter(array(array(
     *          'property' => 'name'
     *          'value' => 'A%'
     *      )));
     * </code>
     *
     * <code>
     *      $this->addFilter(array(array(
     *          'property'   => 'number'
     *          'expression' => '>',
     *          'value'      => '500'
     *      )));
     * </code>
     *
     * @return QueryBuilder
     */
    public function addFilter(array $filter)
    {
        foreach ($filter as $exprKey => $where) {
            if (\is_object($where)) {
                $this->andWhere($where);
                continue;
            }

            $operator = null;
            $expression = null;

            if (\is_array($where) && isset($where['property'])) {
                $exprKey = $where['property'];

                if (isset($where['expression']) && !empty($where['expression'])) {
                    $expression = $where['expression'];
                }

                if (isset($where['operator']) && !empty($where['operator'])) {
                    $operator = $where['operator'];
                }

                $where = $where['value'];
            }

            if (!\preg_match('#^[a-z][a-z0-9_.]+$#i', $exprKey)) {
                continue;
            }

            // The return value of uniqid, even w/o parameters, may contain dots in some environments
            // so we make sure to strip those as well
            $parameterKey = \str_replace(['.'], ['_'], $exprKey . \uniqid());
            if (isset($this->alias) && \strpos($exprKey, '.') === false) {
                $exprKey = $this->alias . '.' . $exprKey;
            }

            if ($expression == null) {
                switch (true) {
                    case \is_string($where):
                        $expression = 'LIKE';
                        break;

                    case \is_array($where):
                        $expression = 'IN';
                        break;

                    case $where === null:
                        $expression = 'IS NULL';
                        break;

                    default:
                        $expression = '=';
                        break;
                }
            }

            if ($where === null) {
                $expression = 'IS NULL';
            }

            $exprParameterKey = ':' . $parameterKey;
            if (is_array($where)) {
                $exprParameterKey = '(' . $exprParameterKey . ')';
            }

            $this->operatorValidator->isValid($expression);
            $expression = new Comparison($exprKey, $expression, $where !== null ? $exprParameterKey : null);

            if (isset($operator)) {
                $this->orWhere($expression);
            } else {
                $this->andWhere($expression);
            }

            if ($where !== null) {
                $this->setParameter($parameterKey, $where);
            }
        }

        return $this;
    }

    /**
     * Adds an ordering to the query results.
     *
     * <code>
     *      $this->addOrderBy(array(array(
     *          'property' => 'name'
     *          'direction' => 'DESC'
     *      )));
     * </code>
     *
     * @param string|array $orderBy the ordering expression
     * @param string       $order   the ordering direction
     *
     * @return QueryBuilder
     */
    public function addOrderBy($orderBy, $order = null)
    {
        /** @var array<int, mixed|null> $select */
        $select = $this->getDQLPart('select');
        if (is_array($orderBy)) {
            foreach ($orderBy as $order) {
                if (!isset($order['property']) || !preg_match('#^[a-zA-Z0-9_.]+$#', $order['property'])) {
                    continue;
                }

                if (isset($select[0], $this->alias)
                    && $select[0]->count() === 1
                    && strpos($order['property'], '.') === false) {
                    $order['property'] = $this->alias . '.' . $order['property'];
                }

                if (isset($order['direction']) && $order['direction'] === 'DESC') {
                    $order['direction'] = 'DESC';
                } else {
                    $order['direction'] = 'ASC';
                }

                parent::addOrderBy(
                    $order['property'],
                    $order['direction']
                );
            }
        } else {
            parent::addOrderBy($orderBy, $order);
        }

        return $this;
    }

    /**
     * Overrides the original function to add the SQL_NO_CACHE parameter
     * for each doctrine orm query if the global shopware debug mode is activated.
     *
     * @return \Doctrine\ORM\Query
     */
    public function getQuery()
    {
        $query = parent::getQuery();

        /** @var ModelManager $em */
        $em = $this->getEntityManager();

        if ($em->isDebugModeEnabled() && $this->getType() === self::SELECT) {
            $em->addCustomHints($query, null, false, true);
        }

        return $query;
    }
}
