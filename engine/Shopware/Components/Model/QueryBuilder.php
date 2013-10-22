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

namespace Shopware\Components\Model;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder as BaseQueryBuilder;

/**
 * The Shopware QueryBuilder is an extension of the standard Doctrine QueryBuilder.
 *
 * @category  Shopware
 * @package   Shopware\Components\Model
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class QueryBuilder extends BaseQueryBuilder
{
    /**
     * @var string
     */
    protected $alias;

    /**
     * @param string $alias
     * @return QueryBuilder
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
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
     * @param array $filter
     * @return QueryBuilder
     */
    public function addFilter(array $filter)
    {
        foreach ($filter as $exprKey => $where) {
            if (is_object($where)) {
                $this->andWhere($where);
                continue;
            }

            $operator = null;
            $expression = null;

            if (is_array($where) && isset($where['property'])) {
                $exprKey = $where['property'];
                $expression = isset($where['expression']) ? $where['expression'] : null;
                $operator = isset($where['operator']) ? $where['operator'] : null;
                $where = $where['value'];
            }

            if (!preg_match('#^[a-z][a-z0-9_.]+$#i', $exprKey)) {
                continue;
            }

            $parameterKey = str_replace(array('.'), array('_') , $exprKey);
            if (isset($this->alias) && strpos($exprKey, '.') === false) {
                $exprKey = $this->alias . '.' . $exprKey;
            }

            if (null == $expression) {
                switch (true) {
                    case is_string($where):
                        $expression = 'LIKE';
                        break;
                    case is_array($where):
                        $expression = 'IN';
                        break;
                    case is_null($where):
                        $expression = 'IS NULL';
                        break;
                    default:
                        $expression = '=';
                        break;
                }
            }

            $assocWhere = array();
            if (is_array($where)) {
                // Create one key for every value in the array
                foreach ($where as $key => $value) {
                    $assocWhere[':'.str_replace('.', '_', $exprKey).$key] = $value;
                }
                // Include all keys in the 'IN' expression
                $inSet = '(' . implode(',', array_keys($assocWhere)) . ')';
                $expression = new Expr\Comparison($exprKey, $expression, $inSet);
            } else {
                // Handling of default filter values
                $expression = new Expr\Comparison($exprKey, $expression, $where !== null ? (':' . $parameterKey) : null);
            }

            if (isset($operator)) {
                $this->orWhere($expression);
            } else {
                $this->andWhere($expression);
            }

            if (count($assocWhere) > 0) {
                // Replace all previously created keys with their corresponding values
                foreach ($assocWhere as $key => $value) {
                    $this->setParameter($key, $value);
                }
            } else if($where !== null) {
                // Handling of default filter values
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
     * @param string|array $orderBy The ordering expression.
     * @param string $order The ordering direction.
     * @return QueryBuilder
     */
    public function addOrderBy($orderBy, $order = null)
    {
        /** @var $select \Doctrine\ORM\Query\Expr\Select */
        $select = $this->getDQLPart('select');
        if (is_array($orderBy)) {
            foreach ($orderBy as $order) {
                if (!isset($order['property']) || !preg_match('#^[a-zA-Z0-9_.]+$#', $order['property'])) {
                    continue;
                }

                if (isset($select[0])
                    && $select[0]->count() === 1
                    && isset($this->alias)
                    && strpos($order['property'], '.') === false) {
                    $order['property'] = $this->alias . '.' . $order['property'];
                }

                if (isset($order['direction']) && $order['direction'] == 'DESC') {
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
     * @return \Doctrine\ORM\Query
     */
    public function getQuery()
    {
        $query = parent::getQuery();

        /**@var $em ModelManager*/
        $em = $this->getEntityManager();

        if ($em->isDebugModeEnabled() && $this->getType() === self::SELECT) {
            $em->addCustomHints($query, null, false, true);
        }
        return $query;
    }


}
