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
 * @return void
 * @throws Exception
 */
function smarty_function_compileLess($params, $template)
{
    $time = $params['timestamp'];
	$output = $params['output'];

    /**@var $pathResolver \Shopware\Components\Theme\PathResolver*/
    $pathResolver = Shopware()->Container()->get('theme_path_resolver');

    /**@var $shop \Shopware\Models\Shop\Shop*/
    $shop = Shopware()->Container()->get('shop');

    /**@var $settings \Shopware\Models\Theme\Settings*/
    $settings = Shopware()->Container()->get('theme_service')->getSystemConfiguration(
        \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT
    );

    $files = $pathResolver->getCssFilePaths($shop, $time);

    $urls = array();

    $compile = $settings->getForceCompile();

    foreach($files as $key => $file) {
        $urls[$key] = $pathResolver->formatPathToUrl(
            $file,
            $shop
        );

        if (!file_exists($file)) {
            $compile = true;
        }
    }

    if (!$compile) {
	    // see: http://stackoverflow.com/a/9473886
        $template->assign($output, $urls);
    }

    /**@var $compiler \Shopware\Components\Theme\Compiler*/
    $compiler = Shopware()->Container()->get('theme_compiler');

    $compiler->compileLess($time, $shop->getTemplate(), $shop);

    $template->assign($output, $urls);;
}
