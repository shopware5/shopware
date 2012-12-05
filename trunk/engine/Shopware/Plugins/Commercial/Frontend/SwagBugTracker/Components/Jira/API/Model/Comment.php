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
 * Domain class for JIRA issue comment.
 */
class Comment extends ValueObject
{
    /**
     * The internal comment identifier.
     *
     * @var integer
     */
    protected $id;

    /**
     * The author/external user who has created this comment.
     *
     * @var string
     */
    protected $author;

    /**
     * The comment's main text.
     *
     * @var string
     */
    protected $description;

    /**
     * When was this comment created.
     *
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * Returns the internal comment identifier.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the comment's main text.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the date when this comment was created.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
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
}