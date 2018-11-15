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

    return $file;
}
