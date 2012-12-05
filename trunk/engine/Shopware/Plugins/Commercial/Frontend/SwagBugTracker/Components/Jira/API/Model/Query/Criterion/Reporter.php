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
 * Query criterion that can be used to filter issues by their reporter.
 */
class Reporter extends Criterion
{
    /**
     * @var string
     */
    private $reporter;

    /**
     * Instantiates a new criterion for the given issue reporter.
     *
     * @param string $reporter A issue reporter identifier.
     */
    public function __construct($reporter)
    {
        $this->reporter = $reporter;
    }

    /**
     * Returns the filter for the issue reporter.
     *
     * @return string
     */
    public function getReporter()
    {
        return $this->reporter;
    }
}