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

namespace Shopware\Components\Jira\Core\Mapper;

use \Shopware\Components\Jira\API\Context;
use \Shopware\Components\Jira\API\Model\ValueObject;

/**
 * Abstract base class for mapper implementation.
 */
abstract class Mapper implements \Shopware\Components\Jira\SPI\Mapper\Mapper
{
    /**
     * @var \Shopware\Components\Jira\API\Context
     */
    protected $context;

    /**
     * Instantiates a new mapper instance for the given context.
     *
     * @param \Shopware\Components\Jira\API\Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }
}