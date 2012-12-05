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
 * Domain class for JIRA issue priority.
 */
class IssuePriority extends ValueObject
{
    /**
     * Issue priority visible in the public JIRA UI
     */
    const BLOCKER  = 1,
          CRITICAL = 2,
          MAJOR    = 3,
          MINOR    = 4,
          TRIVIAL  = 5;

    /**
     * Human readable issue priority names.
     */
    const NAME_BLOCKER  = 'Blocker',
          NAME_CRITICAL = 'Critical',
          NAME_MAJOR    = 'Major',
          NAME_MINOR    = 'Minor',
          NAME_TRIVIAL  = 'Trivial',
          NAME_UNKNOWN  = 'Investigate';

    /**
     * Mapping between priority ids and priority names.
     *
     * @var array
     */
    private $names = array(
        self::BLOCKER  => self::NAME_BLOCKER,
        self::CRITICAL => self::NAME_CRITICAL,
        self::MAJOR    => self::NAME_MAJOR,
        self::MINOR    => self::NAME_MINOR,
        self::TRIVIAL  => self::NAME_TRIVIAL
    );

    /**
     * The internal issue priority identifier.
     *
     * @var integer
     */
    protected $id;

    /**
     * Returns the internal issue priority identifier.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the human readable name for this issue priority.
     *
     * @return string
     */
    public function getName()
    {
        if (isset($this->names[$this->id])) {
            return $this->names[$this->id];
        }
        return self::NAME_UNKNOWN;
    }

    /**
     * Returns an array with the available issue priority.
     *
     * @return \Shopware\Components\Jira\API\Model\IssuePriority[]
     */
    public static function getIssuePriority()
    {
        return array(
            new IssuePriority(array('id' => self::BLOCKER)),
            new IssuePriority(array('id' => self::CRITICAL)),
            new IssuePriority(array('id' => self::MAJOR)),
            new IssuePriority(array('id' => self::MINOR)),
            new IssuePriority(array('id' => self::TRIVIAL)),
        );
    }


}