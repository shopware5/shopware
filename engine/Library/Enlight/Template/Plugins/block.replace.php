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
