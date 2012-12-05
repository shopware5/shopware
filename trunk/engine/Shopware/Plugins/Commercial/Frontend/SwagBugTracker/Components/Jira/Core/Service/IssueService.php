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

namespace Shopware\Components\Jira\Core\Service;

use \Shopware\Components\Jira\API\Model\Query;
use \Shopware\Components\Jira\API\Model\Issue;
use \Shopware\Components\Jira\API\Model\IssueCreate;
use \Shopware\Components\Jira\API\Model\IssueUpdate;
use \Shopware\Components\Jira\API\Model\Project;
use \Shopware\Components\Jira\API\Model\SearchResult;

use \Shopware\Components\Jira\SPI\Mapper\Mapper;
use \Shopware\Components\Jira\SPI\Storage\IssueGateway;

/**
 * Default implementation of the Issue service.
 *
 * This implementation uses simple gateway and mapper objects to retrieve or
 * modify issues in the ticketing system.
 */
class IssueService implements \Shopware\Components\Jira\API\IssueService
{
    /**
     * @var \Shopware\Components\Jira\SPI\Storage\IssueGateway
     */
    private $gateway;

    /**
     * @var \Shopware\Components\Jira\SPI\Mapper\Mapper
     */
    private $mapper;

    /**
     * Instantiates a new service instance with the given <b>$mapper</b> and
     * <b>$gateway</b>.
     *
     * @param \Shopware\Components\Jira\SPI\Storage\IssueGateway $gateway
     * @param \Shopware\Components\Jira\SPI\Mapper\Mapper $mapper
     */
    public function __construct(IssueGateway $gateway, Mapper $mapper)
    {
        $this->gateway = $gateway;
        $this->mapper  = $mapper;
    }

    /**
     * Loads an issue by it's internal identifier.
     *
     * @param integer $issueId
     *
     * @return \Shopware\Components\Jira\API\Model\Issue
     * @throws \Shopware\Components\Jira\API\Exception\NotFoundException
     */
    public function load($issueId)
    {
        $values = $this->gateway->fetchById($issueId);
        return $this->mapper->toObject($values);
    }

    /**
     * Loads an issue by it's human readable jira identifier.
     *
     * @param string $key The human readable issue key.
     *
     * @return \Shopware\Components\Jira\API\Model\Issue
     * @throws \Shopware\Components\Jira\API\Exception\NotFoundException
     */
    public function loadByKey($key)
    {
        $values = $this->gateway->fetchByKey($key);
        return $this->mapper->toObject($values);
    }

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
    public function loadIssues(Project $project, Query $query)
    {
        list($total, $rows) = $this->gateway->fetchIssues($project->getId(), $query);

        $issues = array();
        foreach ($rows as $data) {
            $issues[] = $this->mapper->toObject($data);
        }
        return new SearchResult($total, $issues);
    }

    /**
     * Loads all sub issues for the given {@link Issue}.
     *
     * @param \Shopware\Components\Jira\API\Model\Issue $issue Parent issue object.
     *
     * @return \Shopware\Components\Jira\API\Model\Issue[]
     */
    public function loadSubIssues(Issue $issue)
    {
        $issues = array();
        foreach ($this->gateway->fetchSubIssues($issue->getId()) as $data) {
            $issues[] = $this->mapper->toObject($data);
        }
        return $issues;
    }

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
    public function create(IssueCreate $issueCreate)
    {
        $components = array();
        foreach ($issueCreate->getComponents() as $component) {
            $components[] = $component->getId();
        }

        $versions = array();
        foreach ($issueCreate->getVersions() as $version) {
            $versions[] = $version->getId();
        }

        $key = $this->gateway->store(
            array(
                'project'      =>  $issueCreate->getProject()->getId(),
                'name'         =>  $issueCreate->getName(),
                'description'  =>  $issueCreate->getDescription(),
                'type'         =>  $issueCreate->getType(),
                'remoteUser'   =>  $issueCreate->getRemoteUser(),
                'remoteEmail'  =>  $issueCreate->getRemoteEmail(),
                'keywords'     =>  $issueCreate->getKeywords(),
                'versions'     =>  $versions,
                'components'   =>  $components
            )
        );

        // Due to replication this call may fail
        for ($i = 0; $i < 5; ++$i) {
            try {
                return $this->loadByKey($key);
            } catch (\Exception $e) {
                sleep(1);
            }
        }
        return $this->loadByKey($key);
    }

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
    public function update(Issue $issue, IssueUpdate $issueUpdate)
    {
        $fields = array(
            'name'         =>  $issue->getName(),
            'description'  =>  $issue->getDescription(),
            'type'         =>  $issue->getIssueType()->getId(),
            'votes'        =>  $issue->getVotes(),
        );

        if ($issueUpdate->getName()) {
            $fields['name'] = $issueUpdate->getName();
        }
        if ($issueUpdate->getDescription()) {
            $fields['description'] = $issueUpdate->getDescription();
        }
        if ($issueUpdate->getVotes()) {
            $fields['votes'] = (int) $issueUpdate->getVotes();
        }
        if (is_array($issueUpdate->getKeywords())) {
            $fields['keywords'] = $issueUpdate->getKeywords();
        }
        if ($issueUpdate->getType()) {
            $fields['type'] = (int) $issueUpdate->getType();
        }
        if ($issueUpdate->getComponents()) {
            foreach ($issueUpdate->getComponents() as $i => $component) {
                $fields['components'][$i] = $component->getId();
            }
        }
        if ($issueUpdate->getVersions()) {
            foreach ($issueUpdate->getVersions() as $i => $version) {
                $fields['versions'][$i] = $version->getId();
            }
        }

        $this->gateway->update($issue->getId(), $fields);

        // Due to replication this call may fail
        for ($i = 0; $i < 5; ++$i) {
            try {
                return $this->loadByKey($issue->getKey());
            } catch (\Exception $e) {
                sleep(1);
            }
        }
    }

    /**
     * Removes the given issue.
     *
     * @param \Shopware\Components\Jira\API\Model\Issue $issue Issue that should
     *        be removed from JIRA.
     *
     * @return void
     * @throws \Shopware\Components\Jira\API\Exception\UnauthorizedException
     */
    public function delete(Issue $issue)
    {
        $this->gateway->delete($issue->getId());
    }

    /**
     * Factory method that creates a concrete issue create struct.
     *
     * @param \Shopware\Components\Jira\API\Model\Project $project The context
     *        project that will be the parent of the new issue.
     *
     * @return \Shopware\Components\Jira\API\Model\IssueCreate
     */
    public function newIssueCreate(Project $project)
    {
        return new IssueCreate(
            array(
                'project'  =>  $project,
                'type'     =>  1
            )
        );
    }

    /**
     * Factory method that create a concrete issue update struct.
     *
     * @return \Shopware\Components\Jira\API\Model\IssueUpdate
     */
    public function newIssueUpdate()
    {
        return new IssueUpdate();
    }

}