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
use \Shopware\Components\Jira\API\Model\Project;
use \Shopware\Components\Jira\SPI\Mapper\Mapper;
use \Shopware\Components\Jira\SPI\Storage\VersionGateway;

/**
 * Default implementation of the version service.
 *
 * This implementation utilizes a simple data gateway and mapper object to
 * handle version.
 */
class VersionService implements \Shopware\Components\Jira\API\VersionService
{
    /**
     * @var \Shopware\Components\Jira\SPI\Storage\VersionGateway
     */
    private $gateway;

    /**
     * @var \Shopware\Components\Jira\SPI\Mapper\Mapper
     */
    private $mapper;

    /**
     * Instantiates a new version service instance for the given gateway and
     * object mapper.
     *
     * @param \Shopware\Components\Jira\SPI\Storage\VersionGateway $gateway
     * @param \Shopware\Components\Jira\SPI\Mapper\Mapper $mapper
     */
    public function __construct(VersionGateway $gateway, Mapper $mapper)
    {
        $this->gateway = $gateway;
        $this->mapper  = $mapper;
    }

    /**
     * Loads a single version by it's id.
     *
     * @param integer $id
     *
     * @return \Shopware\Components\Jira\API\Model\Version
     * @throws \Shopware\Components\Jira\API\Exception\NotFoundException
     */
    public function load($id)
    {
        return $this->mapper->toObject($this->gateway->fetchById($id));
    }

    /**
     * Returns all versions affected by the given <b>$issue</b>.
     *
     * @param \Shopware\Components\Jira\API\Model\Issue $issue
     *
     * @return \Shopware\Components\Jira\API\Model\Version[]
     */
    public function loadAffectedByIssue(Issue $issue)
    {
        return $this->mapToVersions(
            $this->gateway->fetchAffectedByIssueId($issue->getId())
        );
    }

    /**
     * Returns all versions where problems in <b>$issue</b> will be fixed.
     *
     * @param \Shopware\Components\Jira\API\Model\Issue $issue
     *
     * @return \Shopware\Components\Jira\API\Model\Version[]
     */
    public function loadFixedByIssue(Issue $issue)
    {
        return $this->mapToVersions(
            $this->gateway->fetchFixedByIssueId($issue->getId())
        );
    }

    /**
     * Loads all versions that are available for the given project.
     *
     * @param \Shopware\Components\Jira\API\Model\Project $project
     *
     * @return \Shopware\Components\Jira\API\Model\Version[]
     */
    public function loadByProject(Project $project)
    {
        return $this->mapToVersions(
            $this->gateway->fetchByProjectId($project->getId())
        );
    }

    /**
     * Takes the given array with raw version data and creates an array with
     * {@link \Shopware\Components\Jira\API\Model\Version} objects.
     *
     * @param array $result
     *
     * @return \Shopware\Components\Jira\API\Model\Version[]
     */
    private function mapToVersions(array $result)
    {
        $versions = array();
        foreach ($result as $data) {
            $versions[] = $this->mapper->toObject($data);
        }
        return $versions;
    }
}