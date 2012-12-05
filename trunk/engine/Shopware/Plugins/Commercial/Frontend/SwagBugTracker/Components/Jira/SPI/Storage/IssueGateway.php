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

namespace Shopware\Components\Jira\SPI\Storage;

use \Shopware\Components\Jira\API\Model\Query;

/**
 * Base interface for an issue gateway that allows read write access to JIRA
 * issues.
 */
interface IssueGateway
{
    /**
     * Reads a single issue by it's internal identifier.
     *
     * @param integer $id
     *
     * @return array
     */
    public function fetchById($id);

    /**
     * Reads a single issue by it's human readable issue key.
     *
     * @param string $key
     *
     * @return array
     */
    public function fetchByKey($key);

    /**
     * Reads issues that belong to the given <b>$projectId</b>.
     *
     * @param integer $projectId
     * @param \Shopware\Components\Jira\API\Model\Query $query
     *        Query object with settings related to the returned issues.
     *
     * @return array[][]
     */
    public function fetchIssues($projectId, Query $query);

    /**
     * Reads all sub issues of the issue identified by <b>$id</b>.
     *
     * @param integer $id
     *
     * @return array[][]
     */
    public function fetchSubIssues($id);

    /**
     * Stores the given issue data in the underlying storage engine. This method
     * will return the project issue key of the newly created issue.
     *
     * @param array $data
     *
     * @return string
     */
    public function store(array $data);

    /**
     * Deletes an issue by it's id.
     *
     * @param integer $id
     *
     * @return void
     */
    public function delete($id);
}