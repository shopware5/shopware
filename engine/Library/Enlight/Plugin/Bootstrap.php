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
 * The Enlight_Plugin_Bootstrap is the class, which each plugin-bootstrap extends from.
 *
 * The Enlight_Plugin_Bootstrap is the basic class for each plugin bootstrap.
 * It has an reference to the application and the plugin collection.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
abstract class Enlight_Plugin_Bootstrap extends Enlight_Class
{
    /**
     * @var string contains the name of the plugin
     */
    protected $name;

    /**
     * @var Enlight_Plugin_PluginCollection Contains an instance of the Enlight_Plugin_PluginCollection
     */
    protected $collection;

    /**
     * The Enlight_Plugin_Bootstrap expects a name for the plugin and
     * optionally an instance of the Enlight_Plugin_PluginCollection
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = (string) $name;
        parent::__construct();
    }

    /**
     * Is executed after the collection has been added.
     */
    public function afterInit()
    {
    }

    /**
     * Getter method for the plugin name property.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter method for the collection property.
     *
     * @param Enlight_Plugin_PluginCollection|null $collection
     *
     * @return Enlight_Plugin_Bootstrap
     */
    public function setCollection(Enlight_Plugin_PluginCollection $collection = null)
    {
        $this->collection = $collection;
        $this->afterInit();

        return $this;
    }

    /**
     * Getter method for the collection property.
     *
     * @return Enlight_Plugin_PluginCollection
     */
    public function Collection()
    {
        return $this->collection;
    }

    /**
     * Returns the application instance of the collection property.
     *
     * @return Shopware
     */
    public function Application()
    {
        return $this->collection->Application();
    }

    /**
     * Get service from resource loader
     *
     * @param string $name
     *
     * @return mixed
     */
    public function get($name)
    {
        return $this->collection->Application()->Container()->get($name);
    }
}
