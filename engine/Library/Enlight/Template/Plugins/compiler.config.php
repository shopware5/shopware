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
 * Function to get access to the Enlight2 Config system.
 *
 * The params array knows the key
 * - name    : Name of the config parameter which should be requested
 * - default : Default value if the queried config key does not exists
 */
class Smarty_Compiler_Config extends Smarty_Internal_CompileBase
{
    /**
     * Array of names of required attribute required by tag
     *
     * @var array
     */
    public $required_attributes = array('name');

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $optional_attributes = array('default', 'namespace');

    /**
     * @param $args
     * @param $compiler
     * @return string
     */
    public function compile($args, $compiler)
    {
        $_attr = $this->getAttributes($compiler, $args);

        if (!Enlight_Application::Instance()->Bootstrap()->hasResource('Config')) {
            if (!isset($_attr['default'])) {
                $_attr['default'] = 'null';
            }
            return '<?php echo ' . $_attr['default'] . '; ?>';
        }

        if (!preg_match('/^([\'"]?)[a-zA-Z0-9]+(\\1)$/', $_attr['name'], $match)) {
            $return = $_attr['name'];
            if (isset($_attr['default'])) {
                $return .= ', ' . $_attr['default'];
            }
            if (isset($_attr['namespace'])) {
                return '<?php echo Enlight_Application::Instance()->Config()->getByNamespace(' . $_attr['namespace'] . ', ' . $return . '); ?>';
            }
            return '<?php echo Enlight_Application::Instance()->Config()->get(' . $return . '); ?>';
        }

        $name = substr($_attr['name'], 1, -1);
        if (isset($_attr['namespace'])) {
            $namespace = substr($_attr['namespace'], 1, -1);
            $value = Enlight_Application::Instance()->Config()->getByNamespace($namespace, $name);
        } else {
            $value = Enlight_Application::Instance()->Config()->get($name);
        }

        if ($value !== null) {
            return '<?php echo ' .  var_export($value, true) . ';?>';
        }
        if (isset($_attr['default'])) {
            return '<?php echo ' .  $_attr['default'] . ';?>';
        }

        return null;
    }
}
