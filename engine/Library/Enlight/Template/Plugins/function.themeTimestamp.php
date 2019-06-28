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
 * Returns the current time measured in the number of seconds
 * since the Unix Epoch (January 1 1970 00:00:00 GMT).
 */
function smarty_function_themeTimestamp($params, $template)
{
    $context = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();
    $shopId = $context->getShop()->getParentId();

    return Shopware()->Container()->get('theme_timestamp_persistor')->getCurrentTimestamp($shopId);
}
