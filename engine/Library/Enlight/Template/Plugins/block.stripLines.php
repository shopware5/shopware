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
 * @param array                    $params
 * @param string                   $content
 * @param Enlight_Template_Default $template
 * @param bool                     $repeat
 *
 * @return string|void
 */
function smarty_block_stripLines($params, $content, $template, $repeat)
{
    if (is_null($content) || $repeat) {
        return;
    }

    $rows = explode("\n", $content);

    return implode("\n", array_filter(array_map('trim', $rows)));
}
