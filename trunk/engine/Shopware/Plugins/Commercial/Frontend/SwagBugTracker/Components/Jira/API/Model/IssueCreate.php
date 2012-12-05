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
 * Struct class used  to specify the properties of a new issue.
 */
class IssueCreate extends ValueObject
{
    /**
     * @var \Shopware\Components\Jira\API\Model\Project
     */
    protected $project;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * The issue type.
     *
     * @var integer
     */
    protected $type;

    /**
     * The remote user.
     *
     * @var string
     */
    protected $remoteUser;
    
    /**
     * Holds the email of the remote user
     *
     * @var unknown_type
     */
    protected $remoteEmail;

    /**
     * @var string[]
     */
    protected $keywords = array();

    /**
     * @var \Shopware\Components\Jira\API\Model\Version[]
     */
    protected $versions = array();

    /**
     * @var \Shopware\Components\Jira\API\Model\Component[]
     */
    protected $components = array();

    /**
     * Returns the context project.
     *
     * @return \Shopware\Components\Jira\API\Model\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function addComponent(Component $component)
    {
        $this->components[] = $component;
    }

    public function getComponents()
    {
        return $this->components;
    }

    /**
     * Adds a version that is affected by the issue.
     *
     * @param \Shopware\Components\Jira\API\Model\Version $version
     *
     * @return void
     */
    public function addVersion(Version $version)
    {
        $this->versions[] = $version;
    }

    /**
     * Returns an array with all Versions that are affected by the issue.
     *
     * @return \Shopware\Components\Jira\API\Model\Version[]
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * Sets the keywords for the new issue.
     *
     * @param string[] $keywords
     *
     * @return void
     */
    public function setKeywords(array $keywords)
    {
        $this->keywords = $keywords;
    }

    /**
     * Returns the keywords for the new issue.
     *
     * @return string[]
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Sets the name of a remote user.
     *
     * @param string $remoteUser
     *
     * @return void
     */
    public function setRemoteUser($remoteUser)
    {
        $this->remoteUser = $remoteUser;
    }

    /**
     * Returns the name of the remote user.
     *
     * @return string
     */
    public function getRemoteUser()
    {
        return $this->remoteUser;
    }
    
    /**
     * Sets the email of the remote user
     *
     * @param unknown_type $remoteEmail
     * @return unknown
     */
    public function setRemoteEmail($remoteEmail)
    {
    	$this->remoteEmail = $remoteEmail;
    }
    
    /**
     * Returns the email of the remote user
     *
     * @param unknown_type $remoteEmail
     * @return unknown
     */
    public function getRemoteEmail()
    {
    	return $this->remoteEmail;
    }

    /**
     * @param integer $type
     *
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }
}