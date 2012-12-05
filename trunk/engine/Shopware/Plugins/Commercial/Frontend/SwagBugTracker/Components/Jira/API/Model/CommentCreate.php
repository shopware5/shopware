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
 * Struct class used  to specify the properties of a new comment.
 */
class CommentCreate extends ValueObject
{
    /**
     * @var \Shopware\Components\Jira\API\Model\Issue
     */
    protected $issue;

    /**
     * The author/external user who has created this comment.
     *
     * @var string
     */
    protected $author;

    /**
     * @var string
     */
    protected $body;

    /**
     * @return \Shopware\Components\Jira\API\Model\Issue
     */
    public function getIssue()
    {
        return $this->issue;
    }

    /**
     * Sets the comment author/external user identifier.
     *
     * @param string $author
     *
     * @return void
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * Returns the comment author/external user identifier.
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Sets the comment body text.
     *
     * @param string $body
     *
     * @return void
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * Returns the comment body text.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
}