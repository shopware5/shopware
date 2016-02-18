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
 * Shopware Application
 *
 * @category  Shopware
 * @package   Shopware\Bootstrap
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Bootstrap extends Enlight_Bootstrap
{
    /**
     * @var \Shopware\Components\DependencyInjection\Container
     */
    protected $container;

    /**
     * Instance of the enlight application.
     *
     * @var Enlight_Application
     */
    protected $application;

    /**
     * The class constructor sets the instance of the given enlight application into
     * the internal $application property.
     *
     * @param Shopware $application
     */
    public function __construct(Shopware $application)
    {
        $this->setApplication($application);
        $this->container = $application->Container();

        parent::__construct();
    }

    /**
     * Returns the application instance.
     *
     * @return Enlight_Application|Shopware
     */
    public function Application()
    {
        return $this->application;
    }

    /**
     * Sets the application instance into the internal $application property.
     *
     * @param  Enlight_Application $application
     * @return Enlight_Bootstrap
     */
    public function setApplication(Enlight_Application $application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * Adds the given resource to the internal resource list and sets the STATUS_ASSIGNED status.
     * The given name will be used as identifier.
     *
     * @param string $name
     * @param mixed $resource
     * @return Enlight_Bootstrap
     */
    public function registerResource($name, $resource)
    {
        $this->container->set($name, $resource);

        return $this;
    }

    /**
     * Checks if the given resource name is already registered. If not the resource is loaded.
     *
     * @param string $name
     * @return bool
     */
    public function hasResource($name)
    {
        return $this->container->has($name);
    }

    /**
     * Checks if the given resource name is already registered.
     * Unlike as the hasResource method is, if the resource does not exist the resource will not even loaded.
     *
     * @param string $name
     * @return bool
     */
    public function issetResource($name)
    {
        return $this->container->initialized($name);
    }

    /**
     * Getter method for a single resource. If the source is not already registered, this function will
     * load the resource automatically. In case the resource is not found the status STATUS_NOT_FOUND is
     * set and an Enlight_Exception is thrown.
     *
     * @param string $name
     * @return mixed
     */
    public function getResource($name)
    {
        return $this->container->get($name);
    }

    /**
     * Loads the given resource. If the resource is already registered and the status
     * is STATUS_BOOTSTRAP an Enlight_Exception is thrown.
     * The resource is initial by the Enlight_Bootstrap_InitResource event.
     * If this event doesn't exist for the given resource, the resource is initialed
     * by call_user_func.
     * After the resource is initialed the event Enlight_Bootstrap_AfterInitResource is
     * fired. In case an exception is thrown by initializing the resource,
     * Enlight sets the status STATUS_NOT_FOUND for the resource in the resource status list.
     * In case the resource successfully initialed the resource has the status STATUS_LOADED
     *
     * @param string $name
     * @return bool
     */
    public function loadResource($name)
    {
        return $this->container->load($name);
    }

    /**
     * If the given resource is set, the resource and the resource status are removed from the
     * list properties.
     *
     * @param string $name
     * @return Enlight_Bootstrap
     */
    public function resetResource($name)
    {
        $this->container->reset($name);

        return $this;
    }

    /**
     * Returns called resource
     *
     * @param string $name
     * @param array $arguments
     * @deprecated 4.2
     * @return Enlight_Class Resource
     */
    public function __call($name, $arguments = null)
    {
        return $this->container->get($name);
    }
}
