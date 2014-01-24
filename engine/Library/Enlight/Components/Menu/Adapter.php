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
 * Adapter interface for the menu database adapter component.
 *
 * The Enlight_Components_Menu_Adapter interface grants an easy way to implement an own db adapter for enlight menus.
 *
 * @category    Enlight
 * @package     Enlight_Menu
 * @copyright   Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license     http://enlight.de/license     New BSD License
 */
interface Enlight_Components_Menu_Adapter
{
    /**
     * Reads the menu form the storage.
     *
     * @param   Enlight_Components_Menu $menu
     * @return  Enlight_Components_Menu_Adapter_DbTable
     */
    public function read(Enlight_Components_Menu $menu);

    /**
     * Writes the menu to the storage.
     *
     * @param   Enlight_Components_Menu $menu
     * @return  Enlight_Components_Menu_Adapter_DbTable
     */
    public function write(Enlight_Components_Menu $menu);
}
