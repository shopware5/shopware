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
 * @param array  $params
 * @param string $template
 *
 * @throws Exception
 */
function smarty_function_compileJavascript(array $params, $template)
{
    $time = $params['timestamp'];
    $output = $params['output'];

    /** @var \Shopware\Components\Theme\PathResolver $pathResolver */
    $pathResolver = Shopware()->Container()->get('theme_path_resolver');

    /** @var \Shopware\Models\Shop\Shop $shop */
    $shop = Shopware()->Container()->get('shop');

    /** @var \Shopware\Models\Theme\Settings $settings */
    $settings = Shopware()->Container()->get('theme_service')->getSystemConfiguration(
        \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT
    );

    $file = $pathResolver->getJsFilePath($shop, $time);
    $url = $pathResolver->formatPathToUrl($file, $shop);

    if (!$settings->getForceCompile() && file_exists($file)) {
        // see: http://stackoverflow.com/a/9473886
        $template->assign($output, [$url]);

        return;
    }

    /** @var \Shopware\Components\Theme\Compiler $compiler */
    $compiler = Shopware()->Container()->get('theme_compiler');
    $compiler->compileJavascript($time, $shop->getTemplate(), $shop);
    $template->assign($output, [$url]);
}
