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
 * @category    Enlight
 * @package     Enlight_Menu
 * @copyright   Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license     http://enlight.de/license     New BSD License
 * @version     $Id$
 * @author      Heiner Lohaus
 * @author      $Author$
 */

/**
 * Factory for Enlight_Components_Menu_Item classes.
 *
 * Represents a page that is defined by specifying a URI.
 * A specific type to construct can be specified by set the key
 * 'type' in $options. If type is 'uri' or 'mvc', the type will be resolved
 * to Zend_Navigation_Page_Uri or Zend_Navigation_Page_Mvc. Any other value
 * for 'type' will be considered the full name of the class to construct.
 * A valid custom page class must extend Zend_Navigation_Page.
 *
 * @category    Enlight
 * @package     Enlight_Menu
 * @copyright   Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license     http://enlight.de/license     New BSD License
 */
class Enlight_Components_Menu_Item extends Zend_Navigation_Page_Uri
{
    /**
     * Factory for Enlight_Components_Menu_Item classes
     *
     * A specific type to construct can be specified by specifying the key
     * 'type' in $options. If type is 'uri' or 'mvc', the type will be resolved
     * to Zend_Navigation_Page_Uri or Zend_Navigation_Page_Mvc. Any other value
     * for 'type' will be considered the full name of the class to construct.
     * A valid custom page class must extend Zend_Navigation_Page.
     *
     * If 'type' is not given, the type of page to construct will be determined
     * by the following rules:
     * - If $options contains either of the keys 'action', 'controller',
     *   'module', or 'route', a Zend_Navigation_Page_Mvc page will be created.
     * - If $options contains the key 'uri', a Zend_Navigation_Page_Uri page
     *   will be created.
     *
     * @throws Zend_Navigation_Exception   if $options is not array/Zend_Config
     * @throws Zend_Exception              if 'type' is specified and
     *                                     Zend_Loader is unable to load the
     *                                     class
     * @throws Zend_Navigation_Exception   if something goes wrong during
     *                                     instantiation of the page
     * @throws Zend_Navigation_Exception   if 'type' is given, and the specified
     *                                     type does not extend this class
     * @throws Zend_Navigation_Exception   if unable to determine which class
     * @param  Enlight_Config|array $options options used for creating page
     * @return Enlight_Components_Menu_Item a page instance
     */
    public static function factory($options)
    {
        if ($options instanceof Zend_Config) {
            /** @var $options Zend_Config */
            $options = $options->toArray();
        }
        $options['type'] = __CLASS__;
        return parent::factory($options);
    }

    /**
     * Adds a item to the container
     *
     * This method will inject the container as the given page's parent by
     * calling {@link Enlight_Components_Menu_Item::setParent()}.
     *
     * @param   $page
     * @return  Enlight_Components_Menu_Item
     */
    public function addItem($page)
    {
        return $this->addPage($page);
    }

    /**
     * Adds several pages at once
     *
     * @param   $pages
     * @return  Enlight_Components_Menu_Item
     */
    public function addItems($pages)
    {
        return $this->addPages($pages);
    }
}
