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

namespace Shopware\Components\Jira\API;

use \Shopware\Components\Jira\API\Model\Query;
use \Shopware\Components\Jira\API\Model\Issue;
use \Shopware\Components\Jira\API\Model\IssueCreate;
use \Shopware\Components\Jira\API\Model\IssueUpdate;
use \Shopware\Components\Jira\API\Model\Project;

/**
 * Base interface
 */
interface IssueService
{
    /**
     * Loads an issue by it's internal identifier.
     *
     * @param integer $issueId The internal issue identifier.
     *
     * @return \Shopware\Components\Jira\API\Model\Issue
     * @throws \Shopware\Components\Jira\API\Exception\NotFoundException
     */
    public function load($issueId);

    /**
     * Loads an issue by it's human readable jira identifier.
     *
     * @param string $key The human readable issue key.
     *
     * @return \Shopware\Components\Jira\API\Model\Issue
     * @throws \Shopware\Components\Jira\API\Exception\NotFoundException
     */
    public function loadByKey($key);

    /**
     * Loads all issues within the given project.
     *
     * @param \Shopware\Components\Jira\API\Model\Project $project
     *        The context project instance.
     * @param \Shopware\Components\Jira\API\Model\Query $query
     *        Query object with settings related to the returned issues.
     *
     * @return \Shopware\Components\Jira\API\Model\SearchResult
     */
    public function loadIssues(Project $project, Query $query);

    /**
     * Loads all sub issues for the given {@link Issue}.
     *
     * @param \Shopware\Components\Jira\API\Model\Issue $issue Parent issue object.
     *
     * @return \Shopware\Components\Jira\API\Model\Issue[]
     */
    public function loadSubIssues(Issue $issue);

    /**
     * Creates a new issue from the values present in <b>$issueCreate</b> and
     * returns the newly create issue.
     *
     * @param \Shopware\Components\Jira\API\Model\IssueCreate $issueCreate The
     *        issue create struct with all mandatory data.
     *
     * @return \Shopware\Components\Jira\API\Model\Issue
     * @throws \Shopware\Components\Jira\API\Exception\UnauthorizedException
     */
    public function create(IssueCreate $issueCreate);

    /**
     * Updates an existing <b>$issue</b> with those values available in the
     * <p>$issueUpdate</b> instance.
     *
     * @param \Shopware\Components\Jira\API\Model\Issue $issue
     *        The context issue that should be updated.
     * @param \Shopware\Components\Jira\API\Model\IssueUpdate $issueUpdate
     *        Simple struct with the values that should be updated.
     *
     * @return \Shopware\Components\Jira\API\Model\Issue
     * @throws \Shopware\Components\Jira\API\Exception\UnauthorizedException
     */
    public function update(Issue $issue, IssueUpdate $issueUpdate);

    /**
     * Removes the given issue.
     *
     * @param \Shopware\Components\Jira\API\Model\Issue $issue Issue that should
     *        be removed from JIRA.
     *
     * @return void
     * @throws \Shopware\Components\Jira\API\Exception\UnauthorizedException
     */
    public function delete(Issue $issue);

    /**
     * Factory method that creates a concrete issue create struct.
     *
     * @param \Shopware\Components\Jira\API\Model\Project $project The context
     *        project that will be the parent of the new issue.
     *
     * @return \Shopware\Components\Jira\API\Model\IssueCreate
     */
    public function newIssueCreate(Project $project);

    /**
     * Factory method that create a concrete issue update struct.
     *
     * @return \Shopware\Components\Jira\API\Model\IssueUpdate
     */
    public function newIssueUpdate();
}