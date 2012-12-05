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

namespace Shopware\Components\Jira\Core\Storage\Mixed;

use \Shopware\Components\Jira\Core\Rest\Client;

/**
 * Default implementation of the gateway factory.
 *
 * This implementation utilizes mysql/pdo and rest webservices to communicate
 * with the JIRA ticketing system.
 */
class GatewayFactory implements \Shopware\Components\Jira\SPI\Storage\GatewayFactory
{
    /**
     * @var \PDO
     */
    private $connection;

    /**
     * @var \Shopware\Components\Jira\Core\Rest\Client
     */
    private $client;

    /**
     * @var \Shopware\Components\Jira\Core\Storage\Mixed\ProjectGateway
     */
    private $projectGateway;

    /**
     * @var \Shopware\Components\Jira\Core\Storage\Mixed\IssueGateway
     */
    private $issueGateway;

    /**
     * @var \Shopware\Components\Jira\Core\Storage\Mixed\VersionGateway
     */
    private $versionGateway;

    /**
     * @var \Shopware\Components\Jira\Core\Storage\Mixed\KeywordGateway
     */
    private $keywordGateway;

    /**
     * @var \Shopware\Components\Jira\Core\Storage\Mixed\ComponentGateway
     */
    private $componentGateway;

    /**
     * @var \Shopware\Components\Jira\Core\Storage\Mixed\CommentGateway
     */
    private $commentGateway;

    public function __construct(\PDO $connection, Client $client)
    {
        $this->connection = $connection;
        $this->client     = $client;
    }

    /**
     * Returns an issue gateway instance for a concrete backend implementation.
     *
     * @return \Shopware\Components\Jira\SPI\Storage\ProjectGateway
     */
    public function createProjectGateway()
    {
        if (null === $this->projectGateway) {
            $this->projectGateway = new ProjectGateway($this->connection);
        }
        return $this->projectGateway;
    }

    /**
     * Returns an issue gateway instance for a concrete backend implementation.
     *
     * @return \Shopware\Components\Jira\SPI\Storage\IssueGateway
     */
    public function createIssueGateway()
    {
        if (null === $this->issueGateway) {
            $this->issueGateway = new IssueGateway($this->connection, $this->client);
        }
        return $this->issueGateway;
    }

    /**
     * Returns a version gateway instance for a concrete backend implementation.
     *
     * @return \Shopware\Components\Jira\SPI\Storage\VersionGateway
     */
    public function createVersionGateway()
    {
        if (null === $this->versionGateway) {
            $this->versionGateway = new VersionGateway($this->connection);
        }
        return $this->versionGateway;
    }

    /**
     * Returns a keyword gateway instance for a concrete backend implementation.
     *
     * @return \Shopware\Components\Jira\SPI\Storage\KeywordGateway
     */
    public function createKeywordGateway()
    {
        if (null === $this->keywordGateway) {
            $this->keywordGateway = new KeywordGateway($this->connection);
        }
        return $this->keywordGateway;
    }

    /**
     * Returns a component gateway instance for a concrete backend implementation.
     *
     * @return \Shopware\Components\Jira\SPI\Storage\ComponentGateway
     */
    public function createComponentGateway()
    {
        if (null === $this->componentGateway) {
            $this->componentGateway = new ComponentGateway($this->connection);
        }
        return $this->componentGateway;
    }

    /**
     * Returns a comment gateway instance for a concrete backend implementation.
     *
     * @return \Shopware\Components\Jira\SPI\Storage\CommentGateway
     */
    public function createCommentGateway()
    {
        if (null === $this->commentGateway) {
            $this->commentGateway = new CommentGateway(
                $this->connection,
                $this->client
            );
        }
        return $this->commentGateway;
    }
}