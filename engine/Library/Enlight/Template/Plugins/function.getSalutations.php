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

function smarty_function_getSalutations($params, $smarty)
{
    $config = Shopware()->Container()->get('config');
    $salutations = $config->get('shopsalutations');
    $salutations = explode(',', $salutations);

    $snippets = Shopware()->Container()->get('snippets');

    $result = [];
    foreach ($salutations as $salutation) {
        if (strlen(trim($salutation)) === 0) {
            continue;
        }

        $label = $snippets->getNamespace('frontend/salutation')->get($salutation);
        if (strlen(trim($label)) === 0) {
            $label = $salutation;
        }
        $result[$salutation] = $label;
    }

    $smarty->assign($params['variable'], $result);
}
