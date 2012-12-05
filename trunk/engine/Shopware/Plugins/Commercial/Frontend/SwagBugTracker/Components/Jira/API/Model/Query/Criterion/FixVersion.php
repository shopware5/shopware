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

namespace Shopware\Components\Jira\API\Model\Query\Criterion;

use \Shopware\Components\Jira\API\Model\Query\Criterion;

/**
 * Query criterion that can be used to filter issues by their fix version.
 */
class FixVersion extends Criterion
{
    /**
     * @var integer
     */
    private $version;

    /**
     * Instantiates a new criterion for the given version.
     *
     * @param integer $version A single fix version.
     */
    public function __construct($version)
    {
        $this->version = (int) $version;
    }

    /**
     * Returns the filter version.
     *
     * @return integer
     */
    public function getVersion()
    {
        return $this->version;
    }
}