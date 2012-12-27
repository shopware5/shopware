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
 * Builds an Enlight Link based on given controller and action.
 * The params array knows the following key:
 * - file     : this key must be filled with a filename
 * - fullPath : if this key is filled, a whole link will be returned http[s]://....
 */
class Smarty_Compiler_Link extends Smarty_Internal_CompileBase
{

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $required_attributes = array('file');

    /**
     * Array of names of valid option flags
     *
     * @var array
     */
    public $option_flags = array('fullPath');


    /**
     * @param $args
     * @param $compiler
     * @return string
     */
    public function compile($args, $compiler)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        if (empty($_attr['file'])) {
            return false;
        }
        $file = trim($_attr['file'], '"\'');
        $fullPath = !empty($_attr['fullPath']);

        if (preg_match('/^([\'"]?)[a-zA-Z0-9\/\.\-\_]+(\\1)$/', $_attr['file'], $match)) {
            return self::link(array(
                'file' => $file,
                'fullPath' => $fullPath
            ), $compiler);
        }

        // could not optimize |link call, so fallback to regular plugin
        if ($compiler->tag_nocache | $compiler->nocache) {
            $compiler->template->required_plugins['nocache']['link']['function']['file'] = __FILE__;
            $compiler->template->required_plugins['nocache']['link']['function']['function'] = 'Smarty_Compiler_Link::link';
        } else {
            $compiler->template->required_plugins['compiled']['link']['function']['file'] = __FILE__;
            $compiler->template->required_plugins['compiled']['link']['function']['function'] = 'Smarty_Compiler_Link::link';
        }
        return '<?php echo Smarty_Compiler_Link::link(array(' .
            '"file" => ' . $_attr['file'] . ', ' .
            '"fullPath" => ' . var_export($fullPath, true) .
            '), $_smarty_tpl); ?>';
    }

    /**
     * @param $params
     * @param $template
     * @return mixed|string
     */
    public static function link($params, $template)
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
                if($useIncludePath) {
                    if($dir === '.' . DS) {
                        $dir = '';
                    }
                    if(($result = Enlight_Loader::isReadable($dir . $file)) !== false) {
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
                $file = $request->getBasePath() . '/' . $file;
            }
        }

        if(empty($file)) {
            $file = $request->getBasePath() . '/';
        }

        if (strpos($file, '/') === 0 && !empty($params['fullPath'])) {
            $file = $request->getScheme() . '://' . $request->getHttpHost() . $file;
        }

        return $file;
    }
}