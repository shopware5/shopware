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
 * Domain class for JIRA issue types.
 */
class IssueType extends ValueObject
{
    /**
     * Issue types visible in the public JIRA UI
     */
    const TYPE_BUG         = 1,
          TYPE_FEATURE     = 2,
          TYPE_TASK        = 3,
          TYPE_IMPROVEMENT = 4;

    /**
     * Human readable issue type names.
     */
    const NAME_BUG         = 'Bug',
          NAME_FEATURE     = 'Feature',
          NAME_TASK        = 'Task',
          NAME_IMPROVEMENT = 'Improvement',
          NAME_UNKNOWN     = 'Ticket';

    /**
     * Mapping between issue ids and issue names.
     *
     * @var array
     */
    private $names = array(
        self::TYPE_BUG         => self::NAME_BUG,
        self::TYPE_FEATURE     => self::NAME_FEATURE,
        self::TYPE_TASK        => self::NAME_TASK,
        self::TYPE_IMPROVEMENT => self::NAME_IMPROVEMENT,
    );

    /**
     * The internal issue type identifier.
     *
     * @var integer
     */
    protected $id;

    /**
     * Returns the internal issue type identifier.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the human readable name for this issue type.
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
     * Returns an array with the available issue types.
     *
     * @return \Shopware\Components\Jira\API\Model\IssueType[]
     */
    public static function getIssueTypes()
    {
        return array(
            new IssueType(array('id' => self::TYPE_BUG)),
            new IssueType(array('id' => self::TYPE_IMPROVEMENT))
        );
    }


}