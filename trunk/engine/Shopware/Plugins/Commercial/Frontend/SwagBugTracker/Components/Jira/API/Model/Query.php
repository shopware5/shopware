<?php
/**
 * Shopware
 *
 * LICENSE
 *
 * Available through the world-wide-web at this URL:
 * http://shopware.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Shopware
 * @package    Shopware_Components
 * @subpackage Jira
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 */

namespace Shopware\Components\Jira\API\Model;

use \Shopware\Components\Jira\API\Model\Query\Criterion;

/**
 * Simple query object used to filter issues.
 */
class Query extends ValueObject
{
    /**
     * Constants that identify the available order columns.
     */
    const ORDER_BY_SUMMARY     = 'name',
          ORDER_BY_STATUS      = 'status',
          ORDER_BY_PRIORITY    = 'priority',
          ORDER_BY_CREATED_AT  = 'createdAt',
          ORDER_BY_MODIFIED_AT = 'modifiedAt',
          ORDER_BY_REPORTER    = 'reporter',
          ORDER_BY_ASSIGNEE    = 'assignee',
          ORDER_BY_VOTES       = 'votes',
          ORDER_BY_TYPE        = 'type';

    /**
     * Order directions.
     */
    const ORDER_ASC = 'asc',
          ORDER_DESC = 'desc';

    /**
     * List of all valid values for $orderBy
     *
     * @var array
     */
    private $validOrderBy = array(
        self::ORDER_BY_ASSIGNEE,
        self::ORDER_BY_REPORTER,
        self::ORDER_BY_CREATED_AT,
        self::ORDER_BY_MODIFIED_AT,
        self::ORDER_BY_PRIORITY,
        self::ORDER_BY_STATUS,
        self::ORDER_BY_SUMMARY,
        self::ORDER_BY_VOTES,
        self::ORDER_BY_TYPE
    );

    /**
     * Allowed values for $orderDir
     *
     * @var array
     */
    private $validOrderDir = array(
        self::ORDER_DESC,
        self::ORDER_ASC
    );

    /**
     * @var integer
     */
    protected $offset = 0;

    /**
     * @var integer
     */
    protected $length = 25;

    /**
     * @var string
     */
    protected $orderBy = self::ORDER_BY_CREATED_AT;

    /**
     * @var string
     */
    protected $orderDir = self::ORDER_DESC;

    /**
     * List of filter criterion objects.
     *
     * @var \Shopware\Components\Jira\API\Model\Query\Criterion[]
     */
    private $criteria = array();

    /**
     * Sets the current pager offset.
     *
     * @param integer $offset
     *
     * @return void
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * Returns the current pager offset.
     *
     * @return integer
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Sets the number of objects that should be returned.
     *
     * @param integer $length
     *
     * @return void
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * Returns the number of objects to query.
     *
     * @return integer
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Sets the column/field that will be used to order the result.
     *
     * @param string $orderBy
     *
     * @return void
     * @todo Throw a validation exception?
     */
    public function setOrderBy($orderBy)
    {
        if (in_array($orderBy, $this->validOrderBy)) {
            $this->orderBy = $orderBy;
        }
    }

    /**
     * Returns the column/field that will be used to order the result.
     *
     * @return string
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * Sets the order direction.
     *
     * @param string $orderDir
     *
     * @return void
     * @todo Throw a validation exception?
     */
    public function setOrderDir($orderDir)
    {
        if (in_array($orderDir, $this->validOrderDir)) {
            $this->orderDir = $orderDir;
        }
    }

    /**
     * Returns the order direction.
     *
     * @return string
     */
    public function getOrderDir()
    {
        return $this->orderDir;
    }

    /**
     * Adds a filter criterion for this query.
     *
     * @param \Shopware\Components\Jira\API\Model\Query\Criterion $criterion
     *
     * @return void
     */
    public function addCriterion(Criterion $criterion)
    {
        $this->criteria[] = $criterion;
    }

    /**
     * Returns an array with optional filter criterion objects.
     *
     * @return \Shopware\Components\Jira\API\Model\Query\Criterion[]
     */
    public function getCriteria()
    {
        return $this->criteria;
    }
}