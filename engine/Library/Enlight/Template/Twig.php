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
 * @package    Enlight_Template
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
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
 * @package    Enlight_Template
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Template_Twig
{
    protected $assignments = array();
    protected $twig;
    protected $path;

    public function __construct(Enlight_View_Twig $engine, $path)
    {
        $this->twig = $engine;
        $this->path = $path;

        $this->assignments = $engine->getAssign();
    }

    /**
     * Assigns a smarty variable.
     *
     * @param array|string $tpl_var the template variable name(s)
     * @param mixed        $value   the value to assign
     * @param bool         $nocache if true any output of this variable will be not cached
     * @param bool         $scope the scope the variable will have  (local,parent or root)
     * @return Enlight_Template_Default
     */
    public function assign($tpl_var, $value = null, $nocache = false, $scope = null)
    {
        $this->assignments[$tpl_var] = $value;

        return $this;
    }

    /**
     * Clears the given assigned template variable.
     *
     * @param   string|array|null $tpl_var the template variable(s) to clear
     * @param   int               $scope
     * @return  Enlight_Template_Default instance for chaining
     */
    public function clearAssign($tpl_var, $scope = null)
    {
        $this->assignments = array();
        return $this;
    }

    /**
     * Extends a template block by name.
     *
     * @param        $spec
     * @param        $content
     * @param string $mode
     * @return void
     */
    public function extendsBlock($spec, $content, $mode = 'REPLACE')
    {
        return;
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
        $this->block_data[$spec] = array('source' => $content, 'mode' => $mode, 'file' => null);
    }

    /**
     * This function extends the whole template file.
     *
     * @param $templateName
     * @return void
     */
    public function extendsTemplate($templateName)
    {
        return;
    }

    /**
     * Sets the cache id.
     *
     * @param   null $cacheId
     * @return  Enlight_Template_Default
     */
    public function setCacheId($cacheId = null)
    {
        return;
    }

    /**
     * Extends the cache id.
     *
     * @param   null $cacheId
     * @return  Enlight_Template_Default
     */
    public function addCacheId($cacheId)
    {
        return $this;
    }

    /**
     * Returns the instance of the Enlight_Template_Manager
     * @return Enlight_Template_Manager
     */
    public function Engine()
    {
        return $this->twig;
    }

    /**
     * Returns the instance of the Enlight_Template_Default
     * @return Enlight_Template_Default
     */
    public function Template()
    {
        return $this;
    }

    public function getTemplateVars() {
        return array();
    }

    public function fetch()
    {
        return $this->twig->Engine()->render($this->path, $this->assignments);
    }

    public function hasTemplate() {
        return true;
    }
}