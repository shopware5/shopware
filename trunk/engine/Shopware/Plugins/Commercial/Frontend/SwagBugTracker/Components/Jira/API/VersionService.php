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

use \Shopware\Components\Jira\API\Model\Issue;
use \Shopware\Components\Jira\API\Model\Project;

/**
 * Base interface that allows access to JIRA project/issue versions.
 */
interface VersionService
{
    /**
     * Loads a single version by it's id.
     *
     * @param integer $id
     *
     * @return \Shopware\Components\Jira\API\Model\Version
     * @throws \Shopware\Components\Jira\API\Exception\NotFoundException
     */
    public function load($id);

    /**
     * Returns all versions affected by the given <b>$issue</b>.
     *
     * @param \Shopware\Components\Jira\API\Model\Issue $issue
     *
     * @return \Shopware\Components\Jira\API\Model\Version[]
     */
    public function loadAffectedByIssue(Issue $issue);

    /**
     * Returns all versions where problems in <b>$issue</b> will be fixed.
     *
     * @param \Shopware\Components\Jira\API\Model\Issue $issue
     *
     * @return \Shopware\Components\Jira\API\Model\Version[]
     */
    public function loadFixedByIssue(Issue $issue);

    /**
     * Loads all versions that are available for the given project.
     *
     * @param \Shopware\Components\Jira\API\Model\Project $project
     *
     * @return \Shopware\Components\Jira\API\Model\Version[]
     */
    public function loadByProject(Project $project);
}