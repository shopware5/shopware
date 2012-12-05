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

use \Shopware\Components\Jira\API\Model\Comment;
use \Shopware\Components\Jira\API\Model\CommentCreate;
use \Shopware\Components\Jira\API\Model\Issue;

/**
 * Base interface that allows access to JIRA issue comments.
 */
interface CommentService
{
    /**
     * Returns all comments associated with the given <b>$issue</b>
     *
     * @param \Shopware\Components\Jira\API\Model\Issue $issue
     *
     * @return \Shopware\Components\Jira\API\Model\Comment[]
     */
    public function loadByIssue(Issue $issue);

    /**
     * Creates a new comment.
     *
     * @param \Shopware\Components\Jira\API\Model\CommentCreate $commentCreate
     *
     * @return \Shopware\Components\Jira\API\Model\Comment
     * @throws \Shopware\Components\Jira\API\Exception\UnauthorizedException
     */
    public function create(CommentCreate $commentCreate);

    /**
     * Factory method that creates an implementation specific create struct.
     *
     * @param \Shopware\Components\Jira\API\Model\Issue $issue
     *
     * @return \Shopware\Components\Jira\API\Model\CommentCreate
     */
    public function newCommentCreate(Issue $issue);
}