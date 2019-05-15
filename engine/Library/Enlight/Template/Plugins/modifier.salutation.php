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

function smarty_modifier_salutation($salutation)
{
    $snippets = Shopware()->Container()->get('snippets');
    $label = $snippets->getNamespace('frontend/salutation')->get($salutation);
    if (strlen(trim($label)) === 0) {
        return $salutation;
    }

    return $label;
}
