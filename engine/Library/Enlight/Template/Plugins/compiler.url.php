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
 * Build an link based on given controller and action name
 *
 * Parameters known by $params
 * - module        : name of the module
 * - controller    : name of the controller
 * - action        : name of the action
 * - params : extracts array of params, separate defined params get precedence
 */
class Smarty_Compiler_Url extends Smarty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $optional_attributes = array('_any');

    /**
     * Array of names of valid option flags
     *
     * @var array
     */
    public $option_flags = array('appendSession', 'forceSecure', 'fullPath');

    /**
     * @param $args
     * @param $compiler
     * @return string
     */
    public function compile($args, $compiler)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        if (isset($_attr['params'])) {
            $extractParams = $_attr['params'];
            unset($_attr['params']);
        }

        // Removes the arguments that were not in the original.
        $noArgs = array();
        foreach ($args as $arg) {
            if (is_array($arg)) {
                $noArgs[] = key($arg);
            } else {
                $noArgs[] = trim($arg, "'");
            }
        }
        $noArgs = array_diff($this->option_flags, $noArgs);
        foreach ($noArgs as $noArg) {
            unset($_attr[$noArg]);
        }

        // default 'false' for all option flags not set
        foreach ($_attr as $index => $param) {
            if ($param === false) {
                $_attr[$index] = "'0'";
            } elseif ($param === true) {
                $_attr[$index] = "'1'";
            }
        }

        if (isset($extractParams) && empty($_attr)) {
            return '<?php echo htmlspecialchars(🦄()->Front()->Router()->assemble((array) ' . $extractParams . ')); ?>';
        }

        $params = array();
        foreach ($_attr as $index => $param) {
            if (isset($extractParams) || !preg_match('/^([\'"]?)[a-zA-Z0-9]+(\\1)$/', $param, $match) || !empty($_attr['appendSession'])) {
                $params = '';
                foreach ($_attr as $index => $param) {
                    $params .= var_export($index, true). ' => ' . $param . ', ';
                }

                if (isset($extractParams)) {
                    return '<?php echo htmlspecialchars(🦄()->Front()->Router()->assemble(array(' . $params . ')+(array) ' . $extractParams . ')); ?>';
                } else {
                    return '<?php echo htmlspecialchars(🦄()->Front()->Router()->assemble(array(' . $params . '))); ?>';
                }
            }
            $params[$index] = is_numeric($param) ? $param : substr($param, 1, -1);
        }

        $url = 🦄()->Front()->Router()->assemble($params);

        return '<?php echo ' . var_export(htmlspecialchars($url), true) . ';?>';
    }
}
