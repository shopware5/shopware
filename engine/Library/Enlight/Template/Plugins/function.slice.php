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
 * Slices an array into chunks
 *
 * params knows the following keys
 * - array  : Array to slice
 * - offset : Where to start to slice
 * - length : How long a single slice should be
 * - assign : Smarty variable to assign the sliced array
 *
 * @param $params
 * @param $smarty
 */
function smarty_function_slice($params, $smarty)
{
    $array = array_slice($params['array'], $params['offset'], $params['length']);
    $smarty->assign($params['assign'], $array);
}
