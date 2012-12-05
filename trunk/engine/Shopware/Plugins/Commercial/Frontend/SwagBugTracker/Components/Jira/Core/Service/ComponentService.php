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
use \Shopware\Components\Jira\API\Model\IssueCreate;
use \Shopware\Components\Jira\API\Model\IssueUpdate;
use \Shopware\Components\Jira\API\Model\Project;

use \Shopware\Components\Jira\SPI\Mapper\Mapper;
use \Shopware\Components\Jira\SPI\Storage\ComponentGateway;


class ComponentService implements \Shopware\Components\Jira\API\ComponentService
{
    /**
     * @var \Shopware\Components\Jira\SPI\Storage\ComponentGateway
     */
    private $gateway;

    /**
     * @var \Shopware\Components\Jira\SPI\Mapper\Mapper
     */
    private $mapper;

    public function __construct(ComponentGateway $gateway, Mapper $mapper)
    {
        $this->gateway = $gateway;
        $this->mapper  = $mapper;
    }

    /**
     * Reads a single component by it's identifier. 
     *
     * @param integer $id
     *
     * @return \Shopware\Components\Jira\API\Model\Component
     * @throws \Shopware\Components\Jira\API\Exception\NotFoundException
     */
    public function load($id)
    {
        return $this->mapper->toObject($this->gateway->fetchById($id));
    }

    /**
     * Reads all components available in the given <b>$project</b> object.
     *
     * @param \Shopware\Components\Jira\API\Model\Project $project
     *
     * @return \Shopware\Components\Jira\API\Model\Component[]
     */
    public function loadByProject(Project $project)
    {
        $components = array();
        foreach ($this->gateway->fetchByProjectId($project->getId()) as $data) {
            $components[] = $this->mapper->toObject($data);
        }
        return $components;
    }

    /**
     * @param \Shopware\Components\Jira\API\Model\Issue $issue
     *
     * @return \Shopware\Components\Jira\API\Model\Component
     */
    public function loadByIssue(Issue $issue)
    {
        $components = array();
        foreach ($this->gateway->fetchByIssueId($issue->getId()) as $data) {
            $components[] = $this->mapper->toObject($data);
        }
        return $components;
    }

}