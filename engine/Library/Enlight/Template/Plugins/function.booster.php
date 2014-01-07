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
 * Smarty Plugin - CSS JS Booster
 *
 * This handy smarty plugin provides an easy to use way to work
 * withe the open source project CSS-JS-Booster (https://github.com/Schepp/CSS-JS-Booster)
 * in a smarty like manner. It compresses and minimizes the outputting css
 * and js sources.
 *
 * The plugin is based on the awesome gist from rodneyrehm (https://github.com/rodneyrehm)
 * one of the developers of Smarty, a PHP templating engine.
 * https://gist.github.com/2358670
 *
 * Please note that the whole configuration of the plugin and the underlying
 * Booster library is inline. The used cache directory is our default template
 * cache folder located under "/cache/templates".
 *
 * @example The following example illustrates a css compression. Please note that the "media"-attribute
 * needs to match the "media"-atrribute in the output-command:
 *
 * {booster type="css" media="screen, projection" src="frontend/_resources/styles/style.css"}
 * {booster type="css" media="screen, projection" src="frontend/_resources/styles/colors.css"}
 * {booster type="css" media="screen, projection" src="frontend/_resources/styles/plugins.css"}
 * {booster type="css" media="screen, projection" src="frontend/_resources/styles/enrichments.css"}
 * {booster type="css" media="screen, projection" output=1}
 *
 * @example The following example illustrates a js compression:
 * {booster type="js" src="frontend/_resources/javascript/jquery.shopware.js}
 * {booster type="js" output=1}
 *
 * @category   Enlight
 * @package    Enlight_Template_Plugins
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     $Author$
 * @author     rodneyrehm (https://github.com/rodneyrehm)
 * @author     S.Pohl
 */

// Terminate the path to the document root
if (method_exists(Enlight_Application::Instance(), 'DocPath')) {
    $docPath = Enlight_Application::Instance()->DocPath();
} else {
    $docPath = getcwd() . DIRECTORY_SEPARATOR;
}

// Define booster root and include it
define('DOCPATH', $docPath);
define('BOOSTER_HTDOCS', DOCPATH . 'engine' .  DIRECTORY_SEPARATOR . 'Library' . DIRECTORY_SEPARATOR . 'Booster');
require_once(BOOSTER_HTDOCS . '/booster_inc.php');

function smarty_function_booster(array $params, Smarty_Internal_Template $template)
{
    $root = $template;
    $type = isset($params['type']) ? $params['type'] : null;
    $media = isset($params['media']) ? $params['media'] : null;
    $src = isset($params['src']) ? $params['src'] : null;
    $output = isset($params['output']);

    // sanity checks
    if ($type !== 'css' && $type !== 'js') {
        throw new Exception('{booster type="css|js"} - unknown type "'. $type .'"');
    }

    // TODO: allow arrays of src
    if (!$output && !$src) {
        throw new Exception('{booster src="/some/file.css"} - invalid src "'. $src .'"');
    }

    // get all available templates
    $dirs = (array) $root->smarty->getTemplateDir();
    $dirs += Enlight_Loader::explodeIncludePath();

     // try to find the file on the filesystem
    foreach ($dirs as $dir) {
        $templateDir = str_ireplace(DOCPATH, '', $dir);
        if (file_exists($dir . $src)) {
            $src = $templateDir . $src;
            break;
        }
    }

    $filepath = DOCPATH . $src;
    if (!file_exists($filepath)) {
        throw new Exception('{booster src="'. $src .'"} file not found at "'. $filepath .'"');
    }

    // store stuff in the root template (NOT Smarty)!
    while ($root->parent && $root->parent instanceof Smarty_Internal_Template) {
        $root = $root->parent;
    }

    // create booster
    $booster = $root->getTemplateVars('__booster');
    if (!$booster) {
        $booster = new Booster();
        // TODO: import options from somewhere
        $booster->js_minify = true;
        $booster->css_totalparts = 1;
        $booster->booster_cachedir = '../../../cache/templates';
        $root->assign('__booster', $booster);
    }

    // create cache
    // Note: would've liked a reference, but can't count on getTemplateVars() allowing to return by ref :(
    $cache = $root->getTemplateVars('__booster_cache');

    if (!$cache) {
        $cache = array(
            'css' => array(),
            'js' => array(),
        );
    }

    // make sure paths are relative to the booster directory
    // they are to be provided absolute from the doc root
    // booster is supposed to be in the doc root
    // so prepending .. should work fine
    $src = '../../../' . $src;

    $result = '';
    if ($output) {

        // We're using mod_rewrite but the booster builds up an strange
        // path if mod_rewrite is on, so we set it manually to false
        $booster->mod_rewrite = false;
        if ($type == 'css') {

            if (empty($cache['css'][$media])) {
                return $result;
            }
            $booster->css_source = $cache['css'][$media];
            $cache['css'][$media] = array();
            $booster->css_media = $media;
            $result = $booster->css_markup();
        } else {
            $booster->js_source = $cache['js'];
            $cache['js'] = array();
            $result = $booster->js_markup();
        }
    } else {
        if ($type == 'css') {
            $cache['css'][$media][] = $src;
        } else {
            $cache['js'][] = $src;
        }
    }

    $root->assign('__booster_cache', $cache);
    return $result;
}
