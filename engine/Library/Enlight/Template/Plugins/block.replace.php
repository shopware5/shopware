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
 * Replaces a smarty block with an other one.
 * If the PHP multibyte (mb) extension is installed this
 * function is multibyte char aware
 *
 * The first parameter has one 'search' and one 'replace' key
 *
 * @param array  $params
 * @param string $content
 * @param mixed  $smarty
 * @param int    $repeat
 * @param string $template
 * @return string
 */
function smarty_block_replace($params, $content, $smarty, &$repeat, $template)
{
    if (is_null($content)) {
        return;
    }

    if (empty($params['search'])) {
        return $content;
    }
    if (empty($params['replace'])) {
        $params['replace'] = '';
    }

    require_once(SMARTY_PLUGINS_DIR . 'shared.mb_str_replace.php');
    return mb_str_replace($params['search'], $params['replace'], $content);
}
