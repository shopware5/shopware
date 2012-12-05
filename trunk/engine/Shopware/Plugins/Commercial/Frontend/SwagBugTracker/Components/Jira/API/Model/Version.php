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
 * Entity class representing a project version.
 */
class Version extends ValueObject
{
    /**
     * The internal version identifier.
     *
     * @var integer
     */
    protected $id;

    /**
     * The human readable version identifier.
     *
     * @var string
     */
    protected $name;

    /**
     * Additional description of this version.
     *
     * @var string
     */
    protected $description;

    /**
     * When is the release of this version scheduled?
     *
     * @var \DateTime
     */
    protected $releaseDate;

    /**
     * Is this version already released?
     *
     * @var boolean
     */
    protected $released;

    /**
     * Returns the internal version identifier.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the name/versionNo for this release.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the description for this version.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the scheduled release date or <b>NULL</b> when no release date
     * was set.
     *
     * @return \DateTime
     */
    public function getReleaseDate()
    {
        return $this->releaseDate;
    }

    /**
     * Returns <b>true</b> when this version is already released.
     *
     * @return boolean
     */
    public function isReleased()
    {
        return $this->released;
    }
}