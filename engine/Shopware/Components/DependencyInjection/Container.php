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

namespace Shopware\Components\DependencyInjection;

use Symfony\Component\DependencyInjection\Container as BaseContainer;

/**
 * @category  Shopware
 * @package   Shopware\Components\DependencyInjection
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Container extends BaseContainer
{
    /**
     * Constant for the bootstrap status, set before the resource is initialed
     */
    const STATUS_BOOTSTRAP = 0;

    /**
     * Constant for the bootstrap status, set after the resource is successfully initialed
     */
    const STATUS_LOADED = 1;

    /**
     * Constant for the bootstrap status, set if an exception is thrown by the initialisation
     */
    const STATUS_NOT_FOUND = 2;

    /**
     * Constant for the bootstrap status, set when the resource is registered.
     */
    const STATUS_ASSIGNED = 3;

    /**
     * Constant for the bootstrap status, set when the resource throwed an exception.
     */
    const STATUS_EXCEPTION = 4;

    /**
     * Property which contains all registered resources
     *
     * @var array
     */
    protected $resourceList = array();

    /**
     * Property which contains all states for the registered resources.
     *
     * @var array
     */
    protected $resourceStatus = array();

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param \Shopware_Bootstrap $bootstrap
     * @return Container
     */
    public function setBootstrap(\Shopware_Bootstrap $bootstrap)
    {
        parent::set('bootstrap', $bootstrap);

        return $this;
    }

    /**
     * @param \Shopware $application
     * @return Container
     */
    public function setApplication(\Shopware $application)
    {
        parent::set('application', $application);

        return $this;
    }

    /**
     * Adds the given resource to the internal resource list and sets the STATUS_ASSIGNED status.
     * The given name will be used as identifier.
     *
     * @param string $name
     * @param mixed $resource
     * @param string $scope
     * @return Container
     */
    public function set($name, $resource, $scope = 'container')
    {
        $name = $this->getNormalizedId($name);

        parent::set($name, $resource);

        $this->resourceList[$name]   = $resource;
        $this->resourceStatus[$name] = self::STATUS_ASSIGNED;

        if (parent::has('events')) {
            parent::get('events')->notify(
                'Enlight_Bootstrap_AfterRegisterResource_' . $name, array(
                    'subject'  => $this,
                    'resource' => $resource
                )
            );
        }

        return $this;
    }

    /**
     * Checks if the given resource name is already registered. If not the resource is loaded.
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        $name = $this->getNormalizedId($name);

        return isset($this->resourceList[$name]) || $this->load($name);
    }

    /**
     * Checks if the given resource name is already registered.
     * Unlike as the hasResource method is, if the resource does not exist the resource will not even loaded.
     *
     * @param string $name
     * @return bool
     */
    public function initialized($name)
    {
        $name = $this->getNormalizedId($name);

        return isset($this->resourceList[$name]);
    }

    /**
     * @param $id
     * @return string
     */
    public function getNormalizedId($id)
    {
        $id = strtolower($id);

        if (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }

        return $id;
    }

    /**
     * Getter method for a single resource. If the source is not already registered, this function will
     * load the resource automatically. In case the resource is not found the status STATUS_NOT_FOUND is
     * set and an \Exception is thrown.
     *
     * @param string $name
     * @throws \Exception
     * @return mixed
     */
    public function get($name, $invalidBehavior = 1)
    {
        $name = $this->getNormalizedId($name);

        if (!isset($this->resourceStatus[$name])) {
            $this->load($name);
        }

        if ($this->resourceStatus[$name] === self::STATUS_NOT_FOUND) {
            throw new \Exception('Resource "' . $name . '" not found failure');
        }

        // a previous attempt to load the resource resulted in an exception,
        // try to reload the resource to provide the original exception
        // instead of generic "resource not found" message.
        if ($this->resourceStatus[$name] === self::STATUS_EXCEPTION) {
            $this->load($name);
        }

        return $this->resourceList[$name];
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
     * @throws \Exception
     * @throws \Enlight_Exception
     * @return bool
     */
    public function load($name)
    {
        $name = $this->getNormalizedId($name);

        if (isset($this->resourceStatus[$name])) {
            switch ($this->resourceStatus[$name]) {
                case self::STATUS_BOOTSTRAP:
                    throw new \Enlight_Exception('Resource "' . $name . '" can\'t resolve all dependencies');
                case self::STATUS_NOT_FOUND:
                    return false;
                case self::STATUS_ASSIGNED:
                case self::STATUS_LOADED:
                    return true;
                default:
                    break;
            }
        }

        try {
            $this->resourceStatus[$name] = self::STATUS_BOOTSTRAP;
            $event = false;

            if (parent::has('events')) {
                $event = parent::get('events')->notifyUntil(
                    'Enlight_Bootstrap_InitResource_' . $name,
                    array('subject' => $this)
                );
            }

            if ($event) {
                $this->resourceList[$name] = $event->getReturn();
            } elseif (parent::has($name)) {
                $this->resourceList[$name] = parent::get($name);
            }

            if (parent::has('events')) {
                parent::get('events')->notify(
                    'Enlight_Bootstrap_AfterInitResource_' . $name, array('subject' => $this)
                );
            }
        } catch (\Exception $e) {
            $this->resourceStatus[$name] = self::STATUS_EXCEPTION;
            throw $e;
        }

        if (isset($this->resourceList[$name]) && $this->resourceList[$name] !== null) {
            $this->resourceStatus[$name] = self::STATUS_LOADED;
            return true;
        } else {
            $this->resourceStatus[$name] = self::STATUS_NOT_FOUND;
            return false;
        }
    }

    /**
     * If the given resource is set, the resource and the resource status are removed from the
     * list properties.
     *
     * @param string $name
     * @return Container
     */
    public function reset($name)
    {
        $name = $this->getNormalizedId($name);
        if (isset($this->resourceList[$name])) {
            unset($this->resourceList[$name]);
            unset($this->resourceStatus[$name]);
        }
        parent::set($name, null);

        return $this;
    }
}
