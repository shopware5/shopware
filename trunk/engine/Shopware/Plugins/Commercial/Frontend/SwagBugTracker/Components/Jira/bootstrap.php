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

namespace Shopware\Components\Jira;

spl_autoload_register(
    function($class)
    {
        if (0 === strpos($class, __NAMESPACE__ . '\\')) {
            include __DIR__ . '/../../../' . strtr($class, '\\', '/') . '.php';
        }
    }
);

// Load Guzzle HTTP library
require_once __DIR__ . '/Vendor/guzzle.phar';