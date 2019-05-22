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
 * Takes an array and splits that array in to n parts.
 *
 * Known Params
 * - array  : Contains an array
 * - parts  : Number of parts to split
 * - assign : Assign the split array to given smarty variable
 *
 * @param array  $params
 * @param object $smarty
 */
function smarty_function_partition($params, $smarty)
{
    // read the param key array which contains an array :)
    $list = $params['array'];
    // read the param key parts
    $p = $params['parts'];
    // get the length of array
    $listlen = count($list);
    // calculate partsize
    $partlen = floor($listlen / $p);
    $partrem = $listlen % $p;
    $partition = [];
    $mark = 0;
    // Split array in to chunks
    for ($px = 0; $px < $p; ++$px) {
        $incr = ($px < $partrem) ? $partlen + 1 : $partlen;
        $partition[$px] = array_slice($list, $mark, $incr);
        $mark += $incr;
    }
    $smarty->assign($params['assign'], $partition);
}
