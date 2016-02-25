<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Takes an array and splits that array in to n parts.
 *
 * Known Params
 * - array  : Contains an array
 * - parts  : Number of parts to split
 * - assign : Assign the split array to given smarty variable
 *
 * @param $params
 * @param $smarty
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
    $partition = array();
    $mark = 0;
    // Split array in to chunks
    for ($px = 0; $px < $p; $px++) {
        $incr = ($px < $partrem) ? $partlen + 1 : $partlen;
        $partition[$px] = array_slice($list, $mark, $incr);
        $mark += $incr;
    }
    $smarty->assign($params['assign'], $partition);
}
