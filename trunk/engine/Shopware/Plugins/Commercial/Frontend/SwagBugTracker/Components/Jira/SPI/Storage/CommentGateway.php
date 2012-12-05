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
 * Base interface for a gateway that provides read/write access to JIRA comments.
 */
interface CommentGateway
{
    /**
     * Reads a single comment by it's identifier.
     *
     * @param integer $id
     *
     * @return array
     */
    public function fetchById($id);

    /**
     * Returns all comments for the given issue identifier.
     *
     * @param integer $issueId
     *
     * @return array
     */
    public function fetchByIssueId($issueId);

    /**
     * Stores the given data as a new comment and returns the comments id.
     *
     * @param array $data
     *
     * @return integer
     */
    public function store(array $data);
}