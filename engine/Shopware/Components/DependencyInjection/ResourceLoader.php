<?php

namespace Shopware\Components\DependencyInjection;

/**
 * @category  Shopware
 * @package   Shopware\Components\DependencyInjection
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ResourceLoader
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
     * @var \Shopware_Bootstrap
     */
    protected $bootstrap;


    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->container->setResourceLoader($this);
    }

    /**
     * @param \Shopware_Bootstrap $bootstrap
     * @return ResourceLoader
     */
    public function setBootstrap(\Shopware_Bootstrap $bootstrap)
    {
        $this->bootstrap = $bootstrap;
        $this->container->set('bootstrap', $bootstrap);

        return $this;
    }

    /**
     * @param \Shopware $application
     * @return ResourceLoader
     */
    public function setApplication(\Shopware $application)
    {
        $this->container->set('application', $application);

        return $this;
    }

    /**
     * Adds the given resource to the internal resource list and sets the STATUS_ASSIGNED status.
     * The given name will be used as identifier.
     *
     * @param string $name
     * @param mixed $resource
     * @return ResourceLoader
     */
    public function set($name, $resource)
    {
        $name = $this->container->getNormalizedId($name);

        $this->container->set($name, $resource);

        $this->resourceList[$name] = $resource;
        $this->resourceStatus[$name] = self::STATUS_ASSIGNED;

        if ($this->container->has('events')) {
            $this->container->getService('events')->notify(
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
        $name = $this->container->getNormalizedId($name);

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
        $name = $this->container->getNormalizedId($name);

        return isset($this->resourceList[$name]);
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
    public function get($name)
    {
        $name = $this->container->getNormalizedId($name);

        if (!isset($this->resourceStatus[$name])) {
            $this->load($name);
        }

        if ($this->resourceStatus[$name] === self::STATUS_NOT_FOUND) {
            throw new \Exception('Resource "' . $name . '" not found failure');
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
        $name = $this->container->getNormalizedId($name);

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

            if ($this->container->has('events')) {
                $event = $this->container->getService('events')->notifyUntil(
                    'Enlight_Bootstrap_InitResource_' . $name,
                    array('subject' => $this)
                );
            }

            if ($event) {
                $this->resourceList[$name] = $event->getReturn();
            } elseif ($this->container->has($name)) {
                $this->resourceList[$name] = $this->container->getService($name);
            }

            if ($this->container->has('events')) {
                $this->container->getService('events')->notify(
                    'Enlight_Bootstrap_AfterInitResource_' . $name, array('subject' => $this)
                );
            }
        } catch (\Exception $e) {
            $this->resourceStatus[$name] = self::STATUS_NOT_FOUND;
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
     * @return ResourceLoader
     */
    public function reset($name)
    {
        $name = $this->container->getNormalizedId($name);
        if (isset($this->resourceList[$name])) {
            unset($this->resourceList[$name]);
            unset($this->resourceStatus[$name]);
        }
        $this->container->set($name, null);

        return $this;
    }
}
