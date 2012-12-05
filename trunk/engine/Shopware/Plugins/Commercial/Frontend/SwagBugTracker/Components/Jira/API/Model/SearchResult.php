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
 * Search results for issues.
 */
class SearchResult extends \ArrayObject
{
    /**
     * @var integer
     */
    private $total;

    /**
     * Instantiates a new search result.
     *
     * @param integer $total
     * @param array $issues
     */
    public function __construct($total, array $issues)
    {
        parent::__construct($issues);

        $this->total = (int) $total;
    }

    /**
     * Returns the issues available in this search result.
     *
     * @return \Shopware\Components\Jira\API\Model\Issue[]
     */
    public function getIssues()
    {
        return $this->getArrayCopy();
    }

    /**
     * Returns the total number of matching issues.
     *
     * @return integer
     */
    public function getTotal()
    {
        return $this->total;
    }
}