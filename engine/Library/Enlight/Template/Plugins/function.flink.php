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
 * @return bool|mixed|string
 */
function smarty_function_flink($params, $template)
{
    $file = $params['file'];

    /** @var $front Enlight_Controller_Front */
    $front = Shopware()->Front();
    $request = $front->Request();

    // check if we got an URI or a local link
    if (!empty($file) && strpos($file, '/') !== 0 && strpos($file, '://') === false) {
        $useIncludePath = $template->smarty->getUseIncludePath();

        // try to find the file on the filesystem
        foreach ($template->smarty->getTemplateDir() as $dir) {
            if (file_exists($dir . $file)) {
                $file = Enlight_Loader::realpath($dir) . DS . str_replace('/', DS, $file);
                break;
            }
            if ($useIncludePath) {
                if ($dir === '.' . DS) {
                    $dir = '';
                }
                if (($result = Enlight_Loader::isReadable($dir . $file)) !== false) {
                    $file = $result;
                    break;
                }
            }
        }

        if (method_exists(Shopware(), 'DocPath')) {
            $docPath = Shopware()->DocPath();
        } else {
            $docPath = getcwd() . DIRECTORY_SEPARATOR;
        }

        // some clean up code
        if (strpos($file, $docPath) === 0) {
            $file = substr($file, strlen($docPath));
        }

        // make sure we have the right separator for the web context
        if (DIRECTORY_SEPARATOR !== '/') {
            $file = str_replace(DIRECTORY_SEPARATOR, '/', $file);
        }
        if (strpos($file, './') === 0) {
            $file = substr($file, 2);
        }
        // if we did not find the file, we are returning a false
        if (strpos($file, '/') !== 0) {
            if (!file_exists($docPath . $file)) {
                //return false;
            }
            if ($request !== null) {
                $file = $request->getBasePath() . '/' . $file;
            }
        }
    }

    if (empty($file) && $request !== null) {
        $file = $request->getBasePath() . '/';
    }

    if ($request !== null && strpos($file, '/') === 0 && !empty($params['fullPath'])) {
        $file = $request->getScheme() . '://' . $request->getHttpHost() . $file;
    }

    return $file;
}
