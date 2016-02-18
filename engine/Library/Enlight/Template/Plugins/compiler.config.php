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
