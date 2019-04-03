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
 * The Enlight_Hook_HookManager allows to hook class methods.
 *
 * Class methods can be hooked by type before, replace and after.
 * It uses a proxy to hook the class on which the hook is placed,
 * so the class method can be overwritten. If a class is hooked, a proxy will be generated for this class.
 * The generated class extends the origin class and implements the Enlight_Hook_Proxy interface.
 * Instead of the origin methods, the registered hook handler methods will be executed.
 *
 * The Enlight_Hook_HookManager stores all registered hook handlers, which are registered
 * by the Enlight_Hook_HookSubscriber. Checks whether a class method of the current one has been hooked and executes it.
 *
 * The hook arguments are passed to the handler, the proxy allows the handler to execute
 * by the manager and overwriting the return of the corresponding method.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Hook_HookManager extends Enlight_Class
{
    /**
     * @var null|Enlight_Hook_ProxyFactory instance of the Enlight_Hook_ProxyFactory
     */
    protected $proxyFactory = null;

    /**
     * @var array internal list of all registered hook aliases
     */
    protected $aliases = [];

    /**
     * @var Enlight_Event_EventManager
     */
    protected $eventManager;

    /**
     * @param Enlight_Event_EventManager $eventManager
     * @param Enlight_Loader             $loader
     * @param array                      $options
     *
     * @throws Exception
     */
    public function __construct(\Enlight_Event_EventManager $eventManager, \Enlight_Loader $loader, $options)
    {
        $this->eventManager = $eventManager;

        if (!isset($options['proxyNamespace'])) {
            throw new \Exception('proxyNamespace has to be set.');
        }

        if (!isset($options['proxyDir'])) {
            throw new \Exception('proxyDir has to be set.');
        }

        $this->proxyFactory = new Enlight_Hook_ProxyFactory(
            $this,
            $options['proxyNamespace'],
            $options['proxyDir']
        );

        $loader->registerNamespace(
            $options['proxyNamespace'],
            $this->proxyFactory->getProxyDir()
        );
    }

    /**
     * @return Enlight_Hook_ProxyFactory
     */
    public function getProxyFactory()
    {
        return $this->proxyFactory;
    }

    /**
     * @return Enlight_Event_EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Checks if the given class has registered hooks.
     * If a method is given the examination is limited to the method.
     *
     * @param string $class
     * @param string $method
     *
     * @return bool
     */
    public function hasHooks($class, $method)
    {
        return $this->eventManager->hasListeners($this->getHookEvent($class, $method, Enlight_Hook_HookHandler::TypeReplace))
            || $this->eventManager->hasListeners($this->getHookEvent($class, $method, Enlight_Hook_HookHandler::TypeBefore))
            || $this->eventManager->hasListeners($this->getHookEvent($class, $method, Enlight_Hook_HookHandler::TypeAfter));
    }

    /**
     * @param string $class
     * @param string $method
     * @param string $type
     *
     * @return string
     */
    public function getHookEvent($class, $method, $type)
    {
        return Enlight_Hook_HookExecutionContext::createHookEventName(
            (isset($this->aliases[$class])) ? $this->aliases[$class] : $class,
            $method,
            $type
        );
    }

    /**
     * Returns the proxy for the given class. If the Enlight_Hook_ProxyFactory hasn't
     * already instantiated it, the function instantiates it automatically.
     *
     * @param string $class
     *
     * @return mixed
     */
    public function getProxy($class)
    {
        return $this->getProxyFactory()->getProxy($class);
    }

    /**
     * Checks if a proxy exists for the given class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function hasProxy($class)
    {
        return $this->getProxyFactory()->getProxy($class) !== $class;
    }

    /**
     * Creates a new hook execution context using the given $subject, $method and $args and executes it. Finally the
     * execution result is returned.
     *
     * @param Enlight_Hook_Proxy $subject
     * @param string             $method
     * @param array              $args
     *
     * @return mixed
     */
    public function executeHooks(Enlight_Hook_Proxy $subject, $method, array $args)
    {
        $context = new Enlight_Hook_HookExecutionContext(
            $this,
            $subject,
            $method,
            $args
        );

        return $context->execute();
    }

    /**
     * Sets the given name as alias for the given target.
     *
     * @param string $name
     * @param string $target
     *
     * @return Enlight_Hook_HookManager
     */
    public function setAlias($name, $target)
    {
        $this->aliases[$target] = $name;

        return $this;
    }

    /**
     * Returns the alias for the given name.
     *
     * @param string $name
     *
     * @return null|mixed
     */
    public function getAlias($name)
    {
        return isset($this->aliases[$name]) ? $this->aliases[$name] : null;
    }

    /**
     * Resets the aliases and registered hooks.
     *
     * @return Enlight_Hook_HookManager
     */
    public function resetHooks()
    {
        $this->aliases = [];

        return $this;
    }
}
