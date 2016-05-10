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

    /** @var $front Enlight_Controller_Front */
    $front = Shopware()->Front();
    $secure = $front->Request()->isSecure();

    $file = $pathResolver->getCssFilePath($shop, $time);
    $url = $pathResolver->formatPathToUrl($file, $shop, $secure);

    if (!$settings->getForceCompile() && file_exists($file)) {
        // see: http://stackoverflow.com/a/9473886
        $template->assign($output, [$url]);
        return;
    }

    /**@var $compiler \Shopware\Components\Theme\Compiler*/
    $compiler = Shopware()->Container()->get('theme_compiler');
    $compiler->compileLess($time, $shop->getTemplate(), $shop);
    $template->assign($output, [$url]);
}
