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
 * Query criterion that can be used to filter issues by the affected versions.
 */
class AffectedVersion extends Criterion
{
    /**
     * @var integer[]
     */
    private $versions;

    /**
     * Instantiates a new criterion for the given version.
     *
     * @param integer|integer[] $version A single version or an array of versions.
     */
    public function __construct($version)
    {
        $this->versions = (array) $version;
    }

    /**
     * Returns the filter versions.
     *
     * @return integer[]
     */
    public function getVersions()
    {
        return $this->versions;
    }
}