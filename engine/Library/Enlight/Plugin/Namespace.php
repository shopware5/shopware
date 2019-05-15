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
 * The Enlight_Plugin_Namespace is an abstract class to represent the namespace of a single plugin.
 *
 * The Enlight_Plugin_Namespace represents a single plugin namespace. The Enlight_Plugin_Namespace
 * has an reference to the Enlight_Plugin_PluginManager. The name of the Enlight_Plugin_Namespace
 * is used as array key in the Enlight_Plugin_PluginCollection.
 *
 * @category   Enlight
 * @package    Enlight_Plugin
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
abstract class Enlight_Plugin_Namespace extends Enlight_Plugin_PluginCollection
{
    /**
     * @var Enlight_Plugin_PluginManager This property contains an instance of the Enlight_Plugin_PluginManager.
     * Can be set over the setManager() method. Is set automatically when the namespace is registered by
     * the plugin manager.
     */
    protected $manager;

    /**
     * @var string This property contains the name of the namespace. Used as array key in the
     * Enlight_Plugin_PluginCollection.
     */
    protected $name;

    /**
     * The Enlight_Plugin_Namespace class constructor expects the name of the namespace and sets it
     * into the internal property.
     *
     * @param   string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        parent::__construct();
    }

    /**
     * Getter method for the namespace name property.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the application instance of the Enlight_Plugin_PluginManager
     *
     * @return Shopware
     */
    public function Application()
    {
        return $this->manager->Application();
    }

    /**
     * Setter method for the manager property.
     *
     * @param Enlight_Plugin_PluginCollection $manager
     * @return Enlight_Plugin_PluginCollection
     */
    public function setManager(Enlight_Plugin_PluginCollection $manager)
    {
        $this->manager = $manager;
        return $this;
    }

    /**
     * Getter method for the manager property.
     *
     * @return  Enlight_Plugin_PluginManager
     */
    public function Manager()
    {
        return $this->manager;
    }
}
