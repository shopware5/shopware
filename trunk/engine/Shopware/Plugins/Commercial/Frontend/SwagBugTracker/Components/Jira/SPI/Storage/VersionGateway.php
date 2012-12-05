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

/**
 * Base interface for a gateway that provides read/write access to JIRA project
 * versions.
 */
interface VersionGateway
{
    /**
     * Returns a single version for the given id.
     *
     * @param integer $id
     *
     * @return array
     * @throws \Shopware\Components\Jira\API\Exception\NotFoundException
     */
    public function fetchById($id);

    /**
     * Returns a set of versions that are affected by an issue.
     *
     * @param integer $issueId
     *
     * @return array
     */
    public function fetchAffectedByIssueId($issueId);

    /**
     * Returns a set of version where an issue will be fixed.
     *
     * @param string $issueId
     *
     * @return array
     */
    public function fetchFixedByIssueId($issueId);

    /**
     * The method reads all versions that are defined for the project with the
     * given <b>$projectId</b>
     *
     * @param integer $projectId
     *
     * @return array
     */
    public function fetchByProjectId($projectId);
}