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
 * Abstract base class representing a single issue in JIRA.
 */
abstract class Issue extends ValueObject
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var \Shopware\Components\Jira\API\Model\IssueType
     */
    protected $issueType;

    /**
     * @var string
     */
    protected $status;

    /**
     * The issue priority.
     *
     * @var string
     */
    protected $priority;

    /**
     * Number of votes for this ticket.
     *
     * @var integer
     */
    protected $votes;

    /**
     * @var string
     */
    protected $reporter;

    /**
     * @var string
     */
    protected $assignee;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $modifiedAt;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns a human readable issue type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->issueType->getName();
    }

    /**
     * Returns the issue priority.
     *
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Returns the current ticket status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Returns the number of votes for this ticket.
     *
     * @return integer
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * Returns the name of the user who has reported this issue.
     *
     * @return string
     */
    public function getReporter()
    {
        return $this->reporter;
    }

    /**
     * Returns the name of the user who is currently assigned to this issue.
     *
     * @return string
     */
    public function getAssignee()
    {
        return $this->assignee;
    }

    /**
     * Returns a date time object that represents the moment when this issue
     * was created.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Returns a date time object that represents the last modification of this
     * issue.
     *
     * @return \DateTime
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * Returns the underlying issue type.
     *
     * @return \Shopware\Components\Jira\API\Model\IssueType
     */
    public function getIssueType()
    {
        return $this->issueType;
    }

    /**
     * Returns the keywords assigned to this issue.
     *
     * @return \Shopware\Components\Jira\API\Model\Keyword[]
     */
    public abstract function getKeywords();

    /**
     * Returns the components affected by this issue.
     *
     * @return \Shopware\Components\Jira\API\Model\Component[]
     */
    public abstract function getComponents();

    /**
     * Returns the sub issues of this issue.
     *
     * @return \Shopware\Components\Jira\API\Model\Issue[]
     */
    public abstract function getSubIssues();

    /**
     * Returns all versions that are affected by this issue.
     *
     * @return \Shopware\Components\Jira\API\Model\Version[]
     */
    public abstract function getVersions();
}