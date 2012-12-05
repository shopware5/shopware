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

use \Shopware\Components\Jira\API\Model\ValueObject;

/**
 * Base interface for the JIRA API.
 *
 * An instance of this class should be used to retrieve the concrete JIRA service
 * implementations.
 */
interface Context
{
    /**
     * Returns the identifier/name of the current remote user.
     *
     * @return string
     */
    public function getCurrentRemoteUser();

    /**
     * Tests if the current user is allowed to perform an operation like EDIT,
     * UPDATE etc. on the given object.
     *
     * @param string $operation
     * @param \Shopware\Components\Jira\API\Model\ValueObject $object
     *
     * @return boolean
     */
    public function canUser($operation, ValueObject $object = null);

    /**
     * Returns the project service which allows read/write access to projects
     * stored in JIRA.
     *
     * @return \Shopware\Components\Jira\API\ProjectService
     */
    public function getProjectService();

    /**
     * Returns the main issue service which allows read/write operation to issues
     * stored in JIRA.
     *
     * @return \Shopware\Components\Jira\API\IssueService
     */
    public function getIssueService();

    /**
     * Returns a service that allows access to project/issue versions.
     *
     * @return \Shopware\Components\Jira\API\VersionService
     */
    public function getVersionService();

    /**
     * Returns the keyword service which provides access to keywords.
     *
     * @return \Shopware\Components\Jira\API\KeywordService
     */
    public function getKeywordService();

    /**
     * Returns the component service which provides access to project and issue
     * components.
     *
     * @return \Shopware\Components\Jira\API\ComponentService
     */
    public function getComponentService();

    /**
     * Returns the component service which provides access to project and issue
     * comments.
     *
     * @return \Shopware\Components\Jira\API\CommentService
     */
    public function getCommentService();
}