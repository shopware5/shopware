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
 * Base interface for a gateway that provides read/write access to JIRA
 * components.
 */
interface ComponentGateway
{
    /**
     * Returns a single component identified by the given <b>$id</b>.
     *
     * @param integer $id
     *
     * @return array
     * @throws \Shopware\Components\Jira\API\Exception\NotFoundException
     */
    public function fetchById($id);

    /**
     * Returns all components for the given project identifier.
     *
     * @param integer $projectId
     *
     * @return array
     */
    public function fetchByProjectId($projectId);

    /**
     * Returns all components for the given issue identifier.
     *
     * @param integer $issueId
     *
     * @return array
     */
    public function fetchByIssueId($issueId);
}