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
 * Interface for a gateway factory.
 *
 * Your production code should only use an implementation of this interface to
 * retrieve the different gateway object. This allows a simple exchange of the
 * concrete implementation.
 */
interface GatewayFactory
{
    /**
     * Returns an project gateway instance for a concrete backend implementation.
     *
     * @return \Shopware\Components\Jira\SPI\Storage\ProjectGateway
     */
    public function createIssueGateway();

    /**
     * Returns an issue gateway instance for a concrete backend implementation.
     *
     * @return \Shopware\Components\Jira\SPI\Storage\ProjectGateway
     */
    public function createProjectGateway();

    /**
     * Returns a version gateway instance for a concrete backend implementation.
     *
     * @return \Shopware\Components\Jira\SPI\Storage\VersionGateway
     */
    public function createVersionGateway();

    /**
     * Returns a keyword gateway instance for a concrete backend implementation.
     *
     * @return \Shopware\Components\Jira\SPI\Storage\KeywordGateway
     */
    public function createKeywordGateway();

    /**
     * Returns a component gateway instance for a concrete backend implementation.
     *
     * @return \Shopware\Components\Jira\SPI\Storage\ComponentGateway
     */
    public function createComponentGateway();

    /**
     * Returns a comment gateway instance for a concrete backend implementation.
     *
     * @return \Shopware\Components\Jira\SPI\Storage\CommentGateway
     */
    public function createCommentGateway();
}