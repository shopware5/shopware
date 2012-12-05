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
 * Base interface for a component service.
 */
interface ComponentService
{
    /**
     * Reads a single component by it's identifier.
     *
     * @param integer $id
     *
     * @return \Shopware\Components\Jira\API\Model\Component
     * @throws \Shopware\Components\Jira\API\Exception\NotFoundException
     */
    public function load($id);

    /**
     * Reads all components available in the given <b>$project</b> object.
     *
     * @param \Shopware\Components\Jira\API\Model\Project $project
     *
     * @return \Shopware\Components\Jira\API\Model\Component[]
     */
    public function loadByProject(Project $project);

    /**
     * Reads all components associated with the given <b>$issue</b> object.
     *
     * @param \Shopware\Components\Jira\API\Model\Issue $issue
     *
     * @return \Shopware\Components\Jira\API\Model\Component[]
     */
    public function loadByIssue(Issue $issue);
}