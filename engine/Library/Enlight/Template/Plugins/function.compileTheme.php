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
 */
function smarty_function_compileTheme($params, $template)
{
    $time = $params['timestamp'];

    /**@var $pathResolver \Shopware\Components\Theme\PathResolver*/
    $pathResolver = Shopware()->Container()->get('theme_path_resolver');

    /**@var $shop \Shopware\Models\Shop\Shop*/
    $shop = Shopware()->Container()->get('shop');

    /**@var $settings \Shopware\Models\Theme\Settings*/
    $settings = Shopware()->Container()->get('theme_service')->getSystemConfiguration(
        \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT
    );

    $cssFile = $pathResolver->getCssFilePath($shop, $time);
    $jsFile = $pathResolver->getJsFilePath($shop, $time);
    $jsUrl = $pathResolver->getCacheJsUrl($shop, $time);
    $cssUrl = $pathResolver->getCacheCssUrl($shop, $time);

    $result = '
        <link href="' . $cssUrl . '" media="screen" rel="stylesheet" type="text/css" />
        <script src="'. $jsUrl  . '"></script>
    ';

    if (file_exists($jsFile) && file_exists($cssFile) && !$settings->getForceCompile()) {
        return $result;
    }

    /**@var $compiler \Shopware\Components\Theme\Compiler*/
    $compiler = Shopware()->Container()->get('theme_compiler');

    $compiler->compile($time, $shop->getTemplate(), $shop);

    return $result;
}
