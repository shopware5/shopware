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
 * Query criterion that can be used to filter issues by a keyword.
 */
class Key extends Criterion
{
    /**
     * @var string
     */
    private $key;

    /**
     * Instantiates a new criterion for the given issue keyword.
     *
     * @param string $keyword A issue keyword.
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * Returns the filter for the issues with a concrete keyword keyword.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
}