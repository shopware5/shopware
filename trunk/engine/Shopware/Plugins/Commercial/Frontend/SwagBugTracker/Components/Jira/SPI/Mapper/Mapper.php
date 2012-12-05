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

namespace Shopware\Components\Jira\SPI\Mapper;

use \Shopware\Components\Jira\API\Model\ValueObject;

/**
 * Base interface for an object mapper.
 *
 * Concrete implementations of this interface will transform a domain object
 * into a simple array or the other way around.
 */
interface Mapper
{
    /**
     * Takes the given <b>$data</b> array and creates a domain object from this
     * value.
     *
     * @param array $data
     *
     * @return \Shopware\Components\Jira\API\Model\ValueObject
     */
    public function toObject(array $data);
}