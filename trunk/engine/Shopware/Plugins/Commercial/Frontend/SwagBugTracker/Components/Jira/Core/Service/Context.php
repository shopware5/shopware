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

namespace Shopware\Components\Jira\Core\Service;

use \Shopware\Components\Jira\API\Model\Issue;
use \Shopware\Components\Jira\API\Model\ValueObject;
use \Shopware\Components\Jira\SPI\Mapper\MapperFactory;
use \Shopware\Components\Jira\SPI\Storage\GatewayFactory;

class Context implements \Shopware\Components\Jira\API\Context
{
    /**
     * @var \Shopware\Components\Jira\API\ProjectService
     */
    private $projectService;

    /**
     * @var \Shopware\Components\Jira\API\IssueService
     */
    private $issueService;

    /**
     * @var \Shopware\Components\Jira\API\VersionService
     */
    private $versionService;

    /**
     * @var \Shopware\Components\Jira\API\KeywordService
     */
    private $keywordService;

    /**
     * @var \Shopware\Components\Jira\API\ComponentService
     */
    private $componentService;

    /**
     * @var \Shopware\Components\Jira\API\CommentService
     */
    private $commentService;

    /**
     * @var string
     */
    private $currentUser;

    /**
     * @var \Shopware\Components\Jira\SPI\Mapper\MapperFactory
     */
    private $mapperFactory;

    /**
     * @var \Shopware\Components\Jira\SPI\Storage\GatewayFactory
     */
    private $gatewayFactory;

    public function initialize(
        MapperFactory $mapperFactory,
        GatewayFactory $gatewayFactory,
        $currentUser
    )
    {
        $this->mapperFactory  = $mapperFactory;
        $this->gatewayFactory = $gatewayFactory;
        $this->currentUser    = trim($currentUser) ? $currentUser : null;
    }

    /**
     * Returns the identifier/name of the current remote user.
     *
     * @return string
     */
    public function getCurrentRemoteUser()
    {
        return $this->currentUser;
    }

    /**
     * Tests if the current user is allowed to perform an operation like EDIT,
     * UPDATE etc. on the given object.
     *
     * @param string $operation
     * @param \Shopware\Components\Jira\API\Model\ValueObject $object
     *
     * @return boolean
     */
    public function canUser($operation, ValueObject $object = null)
    {
        if (null === $this->currentUser) {
            false;
        }

        switch ($operation) {
            case 'vote':
            case 'create':
            case 'comment':
                return true;

            case 'edit':
            case 'delete':
                switch (true) {
                    case ($object instanceof Issue):
                        return ($this->currentUser === $object->getReporter());
                }
                break;
        }
        return false;
    }

    /**
     * Returns the project service which allows read/write access to projects
     * stored in JIRA.
     *
     * @return \Shopware\Components\Jira\API\ProjectService
     */
    public function getProjectService()
    {
        if (null === $this->projectService) {
            $this->projectService = new ProjectService(
                $this->gatewayFactory->createProjectGateway(),
                $this->mapperFactory->createProjectMapper()
            );
        }
        return $this->projectService;
    }

    /**
     *
     * @return \Shopware\Components\Jira\API\IssueService
     */
    public function getIssueService()
    {
        if (null === $this->issueService) {
            $this->issueService = new IssueService(
                $this->gatewayFactory->createIssueGateway(),
                $this->mapperFactory->createIssueMapper()
            );
        }
        return $this->issueService;
    }

    /**
     * Returns a service that allows access to project/issue versions.
     *
     * @return \Shopware\Components\Jira\API\VersionService
     */
    public function getVersionService()
    {
        if (null === $this->versionService) {
            $this->versionService = new VersionService(
                $this->gatewayFactory->createVersionGateway(),
                $this->mapperFactory->createVersionMapper()
            );
        }
        return $this->versionService;
    }

    /**
     * @return \Shopware\Components\Jira\API\KeywordService
     */
    public function getKeywordService()
    {
        if (null === $this->keywordService) {
            $this->keywordService = new KeywordService(
                $this->gatewayFactory->createKeywordGateway(),
                $this->mapperFactory->createKeywordMapper()
            );
        }
        return $this->keywordService;
    }

    /**
     * @return \Shopware\Components\Jira\API\ComponentService
     */
    public function getComponentService()
    {
        if (null === $this->componentService) {
            $this->componentService = new ComponentService(
                $this->gatewayFactory->createComponentGateway(),
                $this->mapperFactory->createComponentMapper()
            );
        }
        return $this->componentService;
    }

    /**
     * Returns the component service which provides access to project and issue
     * comments.
     *
     * @return \Shopware\Components\Jira\API\CommentService
     */
    public function getCommentService()
    {
        if (null === $this->commentService) {
            $this->commentService = new CommentService(
                $this->gatewayFactory->createCommentGateway(),
                $this->mapperFactory->createCommentMapper()
            );
        }
        return $this->commentService;
    }
}