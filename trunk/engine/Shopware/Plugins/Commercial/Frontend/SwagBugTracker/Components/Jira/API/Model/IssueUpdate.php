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
 * Struct class used to specify the properties which should be updated on a
 * concrete issue.
 */
class IssueUpdate extends ValueObject
{
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
     * Number of votes for this issue.
     *
     * @var integer
     */
    protected $votes;

    /**
     * @var string[]
     */
    protected $keywords = null;

    /**
     * @var \Shopware\Components\Jira\API\Model\Component[]
     */
    protected $components = array();

    /**
     * @var \Shopware\Components\Jira\API\Model\Version[]
     */
    protected $versions = array();

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

    /**
     * Sets the number of votes for an issue.
     *
     * @param integer $votes
     *
     * @return void
     */
    public function setVotes($votes)
    {
        $this->votes = (int) $votes;
    }

    /**
     * Returns the number of votes for an issue.
     *
     * @return integer
     */
    public function getVotes()
    {
        return $this->votes;
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

    public function addComponent(Component $component)
    {
        $this->components[] = $component;
    }

    public function getComponents()
    {
        return $this->components;
    }

    public function getVersions()
    {
        return $this->versions;
    }

    public function addVersion(Version $version)
    {
        $this->versions[] = $version;
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