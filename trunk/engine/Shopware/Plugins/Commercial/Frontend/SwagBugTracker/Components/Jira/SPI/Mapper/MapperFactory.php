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

namespace Shopware\Components\Jira\SPI\Mapper;

/**
 * Base interface for a mapper factory.
 *
 * Whenever accessing a mapper you should use an implementation of this interface,
 * so that we can easily replace concrete implementations.
 */
interface MapperFactory
{
    /**
     * Returns a mapper that can transform projects into arrays and simple arrays
     * into projects.
     *
     * @return \Shopware\Components\Jira\SPI\Mapper\Mapper
     */
    public function createProjectMapper();

    /**
     * Returns a mapper that can transform versions into arrays and simple arrays
     * into versions.
     *
     * @return \Shopware\Components\Jira\SPI\Mapper\Mapper
     */
    public function createVersionMapper();

    /**
     * Returns a mapper that can transform issues into arrays and simple arrays
     * into issues.
     *
     * @return \Shopware\Components\Jira\SPI\Mapper\Mapper
     */
    public function createIssueMapper();

    /**
     * Returns a mapper that can transform keywords into arrays and simple arrays
     * into keywords.
     *
     * @return \Shopware\Components\Jira\SPI\Mapper\Mapper
     */
    public function createKeywordMapper();

    /**
     * Returns a mapper that can transform components into arrays and simple
     * arrays into components.
     *
     * @return \Shopware\Components\Jira\SPI\Mapper\Mapper
     */
    public function createComponentMapper();

    /**
     * Returns a mapper that can transform comments into arrays and simple
     * arrays into comments.
     *
     * @return \Shopware\Components\Jira\Core\Mapper\CommentMapper
     */
    public function createCommentMapper();
}