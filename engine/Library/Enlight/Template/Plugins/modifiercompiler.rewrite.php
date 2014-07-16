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
 * @package    Enlight_Template_Plugins
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Rewrites a given link
 *
 * @param array $params
 * @param       $compiler
 * @return string
 */
function smarty_modifiercompiler_rewrite($params, $compiler)
{
    return 'Shopware_Plugins_Core_PostFilter_Bootstrap' .
        '::' . 'rewriteLink('
        . $params[0]
        . ', '
        . (empty($params[1]) ? 'NULL' : $params[1])
        . ')';
}
