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
 * Basic class for Enlight menus.
 *
 * A simple container class for Enlight_Components_Menu_Item. Extends the zend navigation class
 * with an adapter ability and the explicit read of the menu entries.
 *
 * @category    Enlight
 * @package     Enlight_Menu
 * @copyright   Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license     http://enlight.de/license     New BSD License
 */
class Enlight_Components_Menu extends Zend_Navigation
{
    /**
     * Default page class for the menu item. Used when one or more page(s) created. (default: Enlight_Components_Menu_Item)
     * @var string
     */
    protected $_defaultPageClass = 'Enlight_Components_Menu_Item';

    /**
     * Components adapter to read and write the menu item. Used in the read and write function.
     * @var Enlight_Components_Menu_Adapter
     */
    protected $_adapter;

    /**
     * Saves the menu on the adapter.
     *
     * @throws  Enlight_Exception
     * @return  Enlight_Components_Menu
     */
    public function write()
    {
        if ($this->_adapter === null) {
            throw new Enlight_Exception('A save handler are required failure');
        }
        $this->_adapter->write($this);
        return $this;
    }

    /**
     * Reads the menu form the adapter.
     *
     * @throws  Enlight_Exception
     * @return  Enlight_Components_Menu
     */
    public function read()
    {
        if ($this->_adapter === null) {
            throw new Enlight_Exception('A save handler are required failure');
        }
        $this->_adapter->read($this);
        return $this;
    }

    /**
     * Sets the adapter instance in the menu.
     *
     * @param   Enlight_Components_Menu_Adapter $adapter
     * @return  Enlight_Components_Menu
     */
    public function setAdapter(Enlight_Components_Menu_Adapter $adapter)
    {
        $this->_adapter = $adapter;
        return $this;
    }

    /**
     * Returns the adapter instance from the menu.
     *
     * @return Enlight_Components_Menu_Adapter
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * Adds a page to the menu.
     *
     * Supports the indication of deeper containers.
     *
     * @param   Enlight_Components_Menu_Item|Zend_Config|array $item
     * @return  Enlight_Components_Menu
     */
    public function addItem($item)
    {
        return $this->addPage($item);
    }

    /**
     * Adds several pages at once
     *
     * Supports the indication of deeper containers.
     *
     * @param   Enlight_Config|array $items
     * @return  Enlight_Components_Menu
     */
    public function addItems($items)
    {
        return $this->addPages($items);
    }

    /**
     * Adds a page to the menu.
     *
     * Supports the indication of deeper containers.
     *
     * @param   Enlight_Components_Menu_Item|Zend_Config|array $page
     * @return  Enlight_Components_Menu
     */
    public function addPage($page)
    {
        if ($page instanceof Zend_Config) {
            $page = $page->toArray();
        }

        if (is_array($page) && isset($page['parent']) && !$page['parent'] instanceof Zend_Navigation_Container) {
            $page['parent'] = $this->findOneBy('id', $page['parent']);
        }

        if (is_array($page)) {
            $page = call_user_func($this->_defaultPageClass . '::factory', $page);
        }

        /** @var Zend_Navigation_Container $container */
        $container = $page->get('parent');
        if ($container instanceof Zend_Navigation_Container) {
            $container->addPage($page);
        } else {
            parent::addPage($page);
        }

        return $this;
    }

    /**
     * Adds several pages at once
     *
     * Supports the indication of deeper containers.
     *
     * @param   Enlight_Config|array $pages
     * @return  Enlight_Components_Menu
     */
    public function addPages($pages)
    {
        if ($pages instanceof Zend_Config) {
            $pages = $pages->toArray();
        }
        while ($page = array_shift($pages)) {
            if ($page instanceof Zend_Config) {
                /** @var Zend_Config $page */
                $page = $page->toArray();
            }
            if (is_array($page) && empty($page['parent'])) {
                unset($page['parent']);
            }
            if (is_array($page) && isset($page['parent']) && !$page['parent'] instanceof Zend_Navigation_Container) {
                $parent = $this->findOneBy('id', $page['parent']);
                if (empty($parent)) {
                    array_push($pages, $page);
                    continue;
                }
                unset($page['parent']);
            } else {
                $parent = $this;
            }
            if (is_array($page)) {
                $page = call_user_func($this->_defaultPageClass . '::factory', $page);
            }
            $parent->addPage($page);
        }
        return $this;
    }
}
