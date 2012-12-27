<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Components_Model
 * @subpackage Model
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

namespace Shopware\Components\Model;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository as BaseRepository,
    Doctrine\ORM\Query\Expr;

/**
 *
 */
class TreeRepository extends BaseRepository
{
    /**
     * Create a new QueryBuilder instance that is pre populated for this entity name
     *
     * @param   string $alias
     * @return  \Doctrine\ORM\QueryBuilder $qb
     * @access  protected
     */
    public function createQueryBuilder($alias)
    {
        $builder = parent::createQueryBuilder($alias);
        $builder->setAlias($alias);
        return $builder;
    }

    /**
     * @param QueryBuilder $builder
     * @param array $filter
     * @deprecated
     * @return \Shopware\Components\Model\QueryBuilder
     */
    public function addFilter(QueryBuilder $builder, array $filter)
    {
        return $builder->addFilter($filter);
    }

    /**
     * @param   mixed $node
     * @param   null|string $fields
     * @return  null|\Doctrine\ORM\Query
     */
    public function getPathByIdQuery($node, $fields = null)
    {
        if (!is_object($node)) {
            $node = $this->find($node);
        }
        if($node === null) {
            return null;
        }
        $builder = $this->getPathQueryBuilder($node);
        if($fields !== null) {
            $builder->resetDQLPart('select');
            $fields = (array) $fields;
            foreach($fields as $key => $field) {
                $fields[$key] = 'node.' . $field;
            }
            $builder->select($fields);
        }
        return $builder->getQuery();
    }

    /**
     * @param mixed $node
     * @param null $field
     * @param null $separator
     * @return array|string
     */
    public function getPathById($node, $field = null, $separator = null)
    {
        $query = $this->getPathByIdQuery($node, $field);
        if($query === null) {
            return null;
        }
        $path = $query->getArrayResult();
        if(is_string($field)) {
            foreach($path as $key => $value) {
                $path[$key] = $value[$field];
            }
            if($separator !== null) {
                $path = implode($separator, $path);
            }
        }
        return $path;
    }

    /**
     * Tries to recover the tree
     *
     * @throws RuntimeException - if something fails in transaction
     * @return bool
     */
    public function recover()
    {
        if ($this->verify() === true) {
            return true;
        }

        while (list($key, $category) = each($categories)) {
            $children = $category->getChildren()->getValues();
            if(empty($children)) {
                continue;
            }
            foreach($children as $child) {
                $child->setParent(null);
                $categories[] = $child;
            }
            $this->_em->flush();
            foreach($children as $child) {
                $child->setParent($category);
            }
            $this->_em->flush();
        }

        return true;
    }
}