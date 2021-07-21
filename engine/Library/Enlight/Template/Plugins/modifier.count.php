<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */

/**
 * Overwrites the default smarty count modifier for PHP8 compatibility reasons
 */
function smarty_modifier_count($value)
{
    // PHP version is below 8.0.0, use default behavior
    if (\PHP_VERSION_ID < 80000) {
        return \count($value);
    }

    // Neither a countable object nor an array
    if (!\is_array($value) && !($value instanceof Countable)) {
        return 0;
    }

    return \count($value);
}
