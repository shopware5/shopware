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

use Shopware\Components\DependencyInjection\Container;

/**
 * @deprecated since 5.2 will be removed in 6.0
 */
class Shopware_Bootstrap
{
    /**
     * @var Container
     */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Returns called resource
     *
     * @deprecated since 5.2 will be removed in 6.0
     *
     * @param string $name
     * @param array  $arguments
     *
     * @deprecated 4.2
     *
     * @return Enlight_Class Resource
     */
    public function __call($name, $arguments = null)
    {
        trigger_error('Shopware()->Bootstrap()->__call() is deprecated since version 5.2 and will be removed in 6.0. Use Shopware()->Container() instead', E_USER_DEPRECATED);

        return $this->container->get($name);
    }

    /**
     * Returns the application instance.
     *
     * @deprecated since 5.2 will be removed in 6.0
     *
     * @return Shopware
     */
    public function Application()
    {
        trigger_error('Shopware()->Bootstrap()->Application() is deprecated since version 5.2 and will be removed in 6.0. Use Shopware()->Container() instead', E_USER_DEPRECATED);

        return $this->container->get('application');
    }

    /**
     * Adds the given resource to the internal resource list and sets the STATUS_ASSIGNED status.
     * The given name will be used as identifier.
     *
     * @deprecated since 5.2 will be removed in 6.0
     *
     * @param string $name
     *
     * @return Shopware_Bootstrap
     */
    public function registerResource($name, $resource)
    {
        trigger_error('Shopware()->Bootstrap()->registerResource() is deprecated since version 5.2 and will be removed in 6.0. Use Shopware()->Container() instead', E_USER_DEPRECATED);

        $this->container->set($name, $resource);

        return $this;
    }

    /**
     * Checks if the given resource name is already registered. If not the resource is loaded.
     *
     * @deprecated since 5.2 will be removed in 6.0
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasResource($name)
    {
        trigger_error('Shopware()->Bootstrap()->hasResource() is deprecated since version 5.2 and will be removed in 6.0. Use Shopware()->Container() instead', E_USER_DEPRECATED);

        return $this->container->has($name);
    }

    /**
     * Checks if the given resource name is already registered.
     * Unlike as the hasResource method is, if the resource does not exist the resource will not even loaded.
     *
     * @deprecated since 5.2 will be removed in 6.0
     *
     * @param string $name
     *
     * @return bool
     */
    public function issetResource($name)
    {
        trigger_error('Shopware()->Bootstrap()->issetResource() is deprecated since version 5.2 and will be removed in 6.0. Use Shopware()->Container() instead', E_USER_DEPRECATED);

        return $this->container->initialized($name);
    }

    /**
     * Getter method for a single resource. If the source is not already registered, this function will
     * load the resource automatically. In case the resource is not found the status STATUS_NOT_FOUND is
     * set and an Enlight_Exception is thrown.
     *
     * @deprecated since 5.2 will be removed in 6.0
     *
     * @param string $name
     */
    public function getResource($name)
    {
        trigger_error('Shopware()->Bootstrap()->getResource() is deprecated since version 5.2 and will be removed in 6.0. Use Shopware()->Container() instead', E_USER_DEPRECATED);

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
     * @deprecated since 5.2 will be removed in 6.0
     *
     * @param string $name
     *
     * @return bool
     */
    public function loadResource($name)
    {
        trigger_error('Shopware()->Bootstrap()->loadResource() is deprecated since version 5.2 and will be removed in 6.0. Use Shopware()->Container() instead', E_USER_DEPRECATED);

        return $this->container->load($name);
    }

    /**
     * If the given resource is set, the resource and the resource status are removed from the
     * list properties.
     *
     * @deprecated since 5.2 will be removed in 6.0
     *
     * @param string $name
     *
     * @return Shopware_Bootstrap
     */
    public function resetResource($name)
    {
        trigger_error('Shopware()->Bootstrap()->resetResource() is deprecated since version 5.2 and will be removed in 6.0. Use Shopware()->Container() instead', E_USER_DEPRECATED);

        $this->container->reset($name);

        return $this;
    }
}
