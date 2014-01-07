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
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
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
    $front = Enlight_Application::Instance()->Front();
    $request = $front->Request();

    // check if we got an URI or a local link
    if (!empty($file) && strpos($file, '/') !== 0 && strpos($file, '://') === false) {

        $useIncludePath = $template->smarty->getUseIncludePath();

        // try to find the file on the filesystem
        foreach ($template->smarty->getTemplateDir() as $dir) {
            if (file_exists($dir . $file)) {
                $file = realpath($dir) . DS . str_replace('/', DS, $file);
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

        if (method_exists(Enlight_Application::Instance(), 'DocPath')) {
            $docPath = Enlight_Application::Instance()->DocPath();
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
