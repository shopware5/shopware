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
 * The Enlight_Template_Default class is an extension of Smarty_Internal_Template to extend the
 * template via smarty blocks.
 *
 * The Enlight_Template_Default extends the Smarty_Internal_Template with a scoping for properties and to
 * extends the template via smarty blocks. With Enlight it is possible to overwrite or extends smarty blocks from
 * multiple plugins.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Template_Default extends Smarty_Internal_Template
{
    /**
     * Constant to overwrite smarty blocks.
     */
    const BLOCK_REPLACE = 'replace';

    /**
     * Constant to add a content before the smarty block
     */
    const BLOCK_APPEND = 'append';

    /**
     * Constant to add a content after the smarty block
     */
    const BLOCK_PREPEND = 'prepend';

    /**
     * Assigns a smarty variable.
     *
     * @param array|string $tpl_var the template variable name(s)
     * @param mixed        $value   the value to assign
     * @param bool         $nocache if true any output of this variable will be not cached
     * @param bool         $scope   the scope the variable will have  (local,parent or root)
     *
     * @return Enlight_Template_Default
     */
    public function assign($tpl_var, $value = null, $nocache = false, $scope = null)
    {
        if ($scope === null || $scope === Smarty::SCOPE_LOCAL) {
            parent::assign($tpl_var, $value, $nocache);
        } elseif ($scope === Smarty::SCOPE_ROOT) {
            $this->smarty->assign($tpl_var, $value, $nocache);
        } elseif ($scope === Smarty::SCOPE_GLOBAL) {
            $this->smarty->assignGlobal($tpl_var, $value, $nocache);
        } elseif ($scope == Smarty::SCOPE_PARENT) {
            if ($this->parent !== null) {
                $this->parent->assign($tpl_var, $value, $nocache);
            } else {
                parent::assign($tpl_var, $value, $nocache);
            }
        }

        return $this;
    }

    /**
     * Clears the given assigned template variable.
     *
     * @param string|array|null $tpl_var the template variable(s) to clear
     * @param int               $scope
     *
     * @return Enlight_Template_Default instance for chaining
     */
    public function clearAssign($tpl_var, $scope = null)
    {
        if ($tpl_var === null) {
            $function = 'clearAllAssign';
        } else {
            $function = 'clearAssign';
        }

        if ($scope === null || $scope === Smarty::SCOPE_LOCAL) {
            parent::$function($tpl_var);
        } elseif ($scope === Smarty::SCOPE_ROOT) {
            parent::$function($tpl_var);
            $this->smarty->$function($tpl_var);
        } elseif ($scope == Smarty::SCOPE_PARENT) {
            parent::$function($tpl_var);
            $this->parent->$function($tpl_var);
        } elseif ($scope === Smarty::SCOPE_GLOBAL) {
            if ($tpl_var === null) {
                Smarty::$global_tpl_vars[$tpl_var] = [];
            } else {
                unset(Smarty::$global_tpl_vars[$tpl_var]);
            }
        }

        return $this;
    }

    /**
     * Extends a template block by name.
     *
     * @param        $spec
     * @param        $content
     * @param string $mode
     */
    public function extendsBlock($spec, $content, $mode = self::BLOCK_REPLACE)
    {
        if ($mode === null) {
            $mode = self::BLOCK_REPLACE;
        }
        $complete = $this->smarty->left_delimiter . '$smarty.block.child' . $this->smarty->right_delimiter;

        if (strpos($content, $complete) !== false) {
            if (isset($this->block_data[$spec])) {
                $content = str_replace(
                    $complete,
                    $this->block_data[$spec]['source'],
                    $content
                );
                unset($this->block_data[$spec]);
            } else {
                $content = str_replace($complete, '', $content);
            }
        }
        if (isset($this->block_data[$spec])) {
            if (strpos($this->block_data[$spec]['source'], '%%%%SMARTY_PARENT%%%%') !== false) {
                $content = str_replace('%%%%SMARTY_PARENT%%%%', $content, $this->block_data[$spec]['source']);
            } elseif ($this->block_data[$spec]['mode'] == 'prepend') {
                $content = $this->block_data[$spec]['source'] . $content;
            } elseif ($this->block_data[$spec]['mode'] == 'append') {
                $content .= $this->block_data[$spec]['source'];
            }
        }
        $this->block_data[$spec] = ['source' => $content, 'mode' => $mode, 'file' => null];
    }

    /**
     * This function extends the whole template file.
     *
     * @param string $templateName
     */
    public function extendsTemplate($templateName)
    {
        $this->template_resource .= '|' . $templateName;
    }

    /**
     * Sets the cache id.
     *
     * @param null $cacheId
     *
     * @return Enlight_Template_Default
     */
    public function setCacheId($cacheId = null)
    {
        if (is_array($cacheId)) {
            $cacheId = implode('|', $cacheId);
        }
        $this->cache_id = (string) $cacheId;

        return $this;
    }

    /**
     * Extends the cache id.
     *
     * @param null $cacheId
     *
     * @return Enlight_Template_Default
     */
    public function addCacheId($cacheId)
    {
        if (is_array($cacheId)) {
            $cacheId = implode('|', $cacheId);
        } else {
            $cacheId = (string) $cacheId;
        }
        if ($this->cache_id === null) {
            $this->cache_id = $cacheId;
        } else {
            $this->cache_id .= '|' . $cacheId;
        }

        return $this;
    }

    /**
     * Returns the instance of the Enlight_Template_Manager
     *
     * @return Enlight_Template_Manager
     */
    public function Engine()
    {
        return $this->smarty;
    }

    /**
     * Returns the instance of the Enlight_Template_Default
     *
     * @return Enlight_Template_Default
     */
    public function Template()
    {
        return $this;
    }
}
