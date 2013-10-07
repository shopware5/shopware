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
 * @package    Enlight_View
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * The Enlight_View_Default class is the responsible representation of the interface between the template manager of smarty
 * and the internal smarty template.
 *
 * The Enlight_View_Default represents the interface between the template manager of smarty and
 * the internal smarty template. This class is responsible for passing the variables to the loaded template,
 * switch the caching or extends the template blocks.
 * Instead to access the template class directly, this manager will be used so the template can be modified afterwards.
 *
 * @category   Enlight
 * @package    Enlight_View
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
interface Enlight_View_EngineInterface extends Enlight_View_Cache
{
    /**
     * Returns the instance of the Enlight_Template_Manager which has been set in the class constructor.
     * @return  Enlight_Template_Manager
     */
    public function Engine();

    /**
     * Returns the instance of the Enlight_Template_Default which will be set by the setTemplate or loadTemplate
     * function.
     *
     * @return  Enlight_Template_Default
     */
    public function Template();

    /**
     * This function sets the default template directory into the internal instance of the Enlight_Template_Manager
     *
     * @param   string|array $path
     * @return  Enlight_View_Default
     */
    public function setTemplateDir($path);

    /**
     * This function adds a template directory into the internal instance of the Enlight_Template_Manager
     *
     * @param   $templateDir
     * @param   null $key
     * @return  Enlight_View_Default
     */
    public function addTemplateDir($templateDir, $key = null);

    /**
     * Sets the current template instance into the internal property.
     *
     * @param   Enlight_Template_Default $template
     * @return  Enlight_View_Default
     */
    public function setTemplate(Enlight_Template_Default $template = null);

    /**
     * Checks if a template is stored.
     *
     * @return  bool
     */
    public function hasTemplate();

    /**
     * Loads a template by name over the Enlight_Template_Manager.
     *
     * @param   string $template_name
     * @return  Enlight_View_Default
     */
    public function loadTemplate($template_name);

    /**
     * Creates a new template by name over the Enlight_Template_Manager.
     *
     * @param   $template_name
     * @return  Enlight_Template_Default
     */
    public function createTemplate($template_name);

    /**
     * This function extends the internal array with the given template.
     * @param   $template_name
     * @return  Enlight_View_Default
     */
    public function extendsTemplate($template_name);

    /**
     * Extends a template block by name.
     *
     * @param          $spec
     * @param          $content
     * @param   string $mode
     * @return  Enlight_View_Default
     */
    public function extendsBlock($spec, $content, $mode);

    /**
     * Checks if the Enlight_Template_Manager stored the given template.
     * @param   $template_name
     * @return  bool
     */
    public function templateExists($template_name);

    /**
     * Assigns a specified value to the template.
     * If no cache or scope given, the default settings for this property will be used.
     *
     * @param   string $spec
     * @param   mixed  $value
     * @param   bool   $nocache
     * @param   int    $scope
     * @return \Enlight_View|\Enlight_View_Default
     */
    public function assign($spec, $value = null, $nocache = null, $scope = null);

    /**
     * Resets a specified value or all values.
     *
     * @param   string $spec
     * @param   int $scope
     * @return  Enlight_View_Default
     */
    public function clearAssign($spec = null, $scope = null);

    /**
     * Returns a specified value or all values.
     *
     * @param   string|null $spec
     * @return  mixed|array
     */
    public function getAssign($spec = null);

    /**
     * Renders the current template.
     *
     * @return  string
     */
    public function render();

    /**
     * Fetch an template by name over the Enlight_Template_Manager.
     *
     * @param   $template_name
     * @return  string
     */
    public function fetch($template_name);

    /**
     * Setter method for the nocache property. Used as default if the parameter is not given in the assign method.
     *
     * @param   bool $value
     * @return  Enlight_View_Default
     */
    public function setNocache($value = true);

    /**
     * Setter method for the scope property. Used as default if the parameter is not given in the assign method.
     * @param   int|null $scope
     * @return  Enlight_View_Default
     */
    public function setScope($scope = null);

    /**
     * Enable or disable the caching within the template.
     *
     * @param   bool $value
     * @return  Enlight_View_Default
     */
    public function setCaching($value = true);

    /**
     * Checks if the template is already cached.
     * @return  bool
     */
    public function isCached();

    /**
     * Sets the cache id into the internal template object.
     *
     * @param   string|array $cache_id
     * @return  Enlight_View_Default
     */
    public function setCacheId($cache_id = null);

    /**
     * Adds a cache id into the internal template object.
     *
     * @param   string|array $cache_id
     * @return  Enlight_View_Default
     */
    public function addCacheId($cache_id);

    /**
     * Returns the cache id of the internal template object.
     * @return  string
     */
    public function getCacheId();
}