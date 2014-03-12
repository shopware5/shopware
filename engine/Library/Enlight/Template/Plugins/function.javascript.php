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
 * @copyright  Copyright © shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */

/**
 * @param $params
 * @param $template
 * @return string
 * @throws Exception
 *
 */
function smarty_function_javascript($params, $template)
{
    $file = $params['file'];
    $time = $params['timestamp'];

    /**@var $pathResolver \Shopware\Components\Theme\PathResolver*/
    $pathResolver = Shopware()->Container()->get('theme_path_resolver');

    /**@var $shop \Shopware\Models\Shop\Shop*/
    $shop = Shopware()->Container()->get('shop');

    $disableCaching = Shopware()->Container()->get('config')->get('disableThemeCaching');

    $path = $pathResolver->buildJsPath($shop, $file, $time);

    $fileName = $pathResolver->buildJsName($shop, $file, $time);

    $url = $shop->getBasePath() .
        DIRECTORY_SEPARATOR .
        $pathResolver->getCacheDirectoryUrl() .
        DIRECTORY_SEPARATOR .
        $fileName;

    if (file_exists($path) && !$disableCaching) {
        return $url;
    }

    /**@var $compiler \Shopware\Components\Theme\Compiler*/
    $compiler = Shopware()->Container()->get('theme_compiler');

    $compiler->compile($time, $shop->getTemplate(), $shop);

    return $url;
}
