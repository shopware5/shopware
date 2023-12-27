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

use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Theme\PathResolver;
use Shopware\Components\Theme\Service;
use Shopware\Models\Theme\Settings;

/**
 * @param array $params
 * @param array $template
 *
 * @throws Exception
 */
function smarty_function_compileLess($params, $template)
{
    $time = $params['timestamp'];
    $output = $params['output'];

    $pathResolver = Shopware()->Container()->get(PathResolver::class);

    $shop = Shopware()->Container()->get('shop');

    /** @var Settings $settings */
    $settings = Shopware()->Container()->get(Service::class)->getSystemConfiguration(
        AbstractQuery::HYDRATE_OBJECT
    );

    $file = $pathResolver->getCssFilePath($shop, $time);
    $url = $pathResolver->formatPathToUrl($file, $shop);

    if (!$settings->getForceCompile() && file_exists($file)) {
        // see: http://stackoverflow.com/a/9473886
        $template->assign($output, [$url]);

        return;
    }

    $compiler = Shopware()->Container()->get('theme_compiler');
    $compiler->compileLess($time, $shop->getTemplate(), $shop);
    $template->assign($output, [$url]);
}
