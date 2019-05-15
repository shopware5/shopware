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
 * @return bool|mixed|string
 */
function smarty_function_flink($params, $template)
{
    $file = $params['file'];

    $request = Shopware()->Front()->Request();
    $docPath = Shopware()->Container()->getParameter('shopware.app.rootdir');

    // Check if we got an URI or a local link
    if (!empty($file) && strpos($file, '/') !== 0 && strpos($file, '://') === false) {
        $useIncludePath = $template->smarty->getUseIncludePath();

        /** @var string[] $templateDirs */
        $templateDirs = $template->smarty->getTemplateDir();

        // Try to find the file on the filesystem
        foreach ($templateDirs as $dir) {
            if (file_exists($dir . $file)) {
                $file = Enlight_Loader::realpath($dir) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
                break;
            }
            if ($useIncludePath) {
                if ($dir === '.' . DIRECTORY_SEPARATOR) {
                    $dir = '';
                }
                if (($result = Enlight_Loader::isReadable($dir . $file)) !== false) {
                    $file = $result;
                    break;
                }
            }
        }

        // Some cleanup code
        if (strpos($file, $docPath) === 0) {
            $file = substr($file, strlen($docPath));
        }

        // Make sure we have the right separator for the web context
        if (DIRECTORY_SEPARATOR !== '/') {
            $file = str_replace(DIRECTORY_SEPARATOR, '/', $file);
        }

        if (strpos($file, './') === 0) {
            $file = substr($file, 2);
        }

        if ($request !== null) {
            $file = $request->getBasePath() . '/' . ltrim($file, '/');
        }
    }

    if (empty($file) && $request !== null) {
        $file = $request->getBasePath() . '/';
    }

    if ($request !== null && !empty($params['fullPath']) && strpos($file, '/') === 0) {
        $file = $request->getScheme() . '://' . $request->getHttpHost() . $file;
    }

    if ($request === null && Shopware()->Container()->initialized('shop')) {
        $shop = Shopware()->Container()->get('shop');
        $scheme = $shop->getSecure() ? 'https' : 'http';

        $host = $scheme . '://' . $shop->getHost();

        if ($shop->getBasePath()) {
            $host .= $shop->getBasePath();
        }

        $file = $host . '/' . ltrim($file, '/');
    }

    return $file;
}
