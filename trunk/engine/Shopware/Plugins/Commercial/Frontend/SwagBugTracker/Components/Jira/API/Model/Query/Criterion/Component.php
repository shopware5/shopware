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
 * Query criterion that can be used to filter issues by on of their components.
 */
class Component extends Criterion
{
    /**
     * @var integer
     */
    private $component;

    /**
     * Instantiates a new criterion for the given component.
     *
     * @param integer $component A filter component
     */
    public function __construct($component)
    {
        $this->component = (int) $component;
    }

    /**
     * Returns the filter for issue component.
     *
     * @return integer
     */
    public function getComponent()
    {
        return $this->component;
    }
}