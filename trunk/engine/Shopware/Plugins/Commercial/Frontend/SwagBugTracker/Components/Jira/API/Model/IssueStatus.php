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

/**
 * Domain class for JIRA issue status.
 */
class IssueStatus extends ValueObject
{
    /**
     * Issue status visible in the public JIRA UI
     */
    const STATUS_OPEN        = 1,
          STATUS_IN_PROGRESS = 3,
          STATUS_REOPENED    = 4,
          STATUS_RESOLVED    = 5,
          STATUS_CLOSED      = 6,
          STATUS_MORE_INFO   = 10012;

    /**
     * Human readable issue type names.
     */
    const NAME_OPEN        = 'Open',
          NAME_IN_PROGRESS = 'In Progress',
          NAME_REOPENED    = 'Reopened',
          NAME_RESOLVED    = 'Resolved',
          NAME_CLOSED      = 'Closed',
          NAME_UNKNOWN     = 'Investigate',
          NAME_MORE_INFO   = 'More information is needed';

    /**
     * Mapping between status ids and status names.
     *
     * @var array
     */
    private $names = array(
        self::STATUS_OPEN        => self::NAME_OPEN,
        self::STATUS_IN_PROGRESS => self::NAME_IN_PROGRESS,
        self::STATUS_REOPENED    => self::NAME_REOPENED,
        self::STATUS_RESOLVED    => self::NAME_RESOLVED,
        self::STATUS_CLOSED      => self::NAME_CLOSED,
        self::STATUS_MORE_INFO => self::NAME_MORE_INFO
    );

    /**
     * The internal issue status identifier.
     *
     * @var integer
     */
    protected $id;

    /**
     * Returns the internal issue status identifier.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the human readable name for this issue status.
     *
     * @return string
     */
    public function getName()
    {
        if (isset($this->names[$this->id])) {
            return $this->names[$this->id];
        }
        return self::NAME_UNKNOWN;
    }

    /**
     * Returns an array with the available issue status.
     *
     * @return \Shopware\Components\Jira\API\Model\IssueStatus[]
     */
    public static function getIssueStatus()
    {
        return array(
            new IssueStatus(array('id' => self::STATUS_OPEN)),
            new IssueStatus(array('id' => self::STATUS_IN_PROGRESS)),
            new IssueStatus(array('id' => self::STATUS_REOPENED)),
            new IssueStatus(array('id' => self::STATUS_RESOLVED)),
            new IssueStatus(array('id' => self::STATUS_CLOSED)),
            new IssueStatus(array('id' => self::STATUS_MORE_INFO)),
        );
    }


}