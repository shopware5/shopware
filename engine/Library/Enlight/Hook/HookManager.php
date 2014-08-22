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
 * @package    Enlight_Hook
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
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
 * @package    Enlight_Hook
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Hook_HookManager extends Enlight_Class
{
    /**
     * @var Enlight_Application
     */
    protected $application;

    /**
     * @var null|Enlight_Hook_ProxyFactory instance of the Enlight_Hook_ProxyFactory.
     */
    protected $proxyFactory = null;

    /**
     * @var array Internal list of all registered hook aliases.
     */
    protected $aliases = array();

    /**
     * @var Enlight_Event_EventManager
     */
    protected $eventManager;

    /**
     * @param Enlight_Event_EventManager $eventManager
     * @param Enlight_Loader $loader
     * @param array $options
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
     * Checks if the given class has registered hooks.
     * If a method is given the examination is limited to the method.
     *
     * @param   $class
     * @param   $method
     * @return  bool
     */
    public function hasHooks($class, $method)
    {
        $eventManager = $this->eventManager;

        return $eventManager->hasListeners($this->getHookEvent($class, $method, 'replace'))
            || $eventManager->hasListeners($this->getHookEvent($class, $method, 'before'))
            || $eventManager->hasListeners($this->getHookEvent($class, $method, 'after'));
    }

    /**
     * Returns all registered hooks of the given arguments.
     *
     * @param   $class
     * @param   $method
     * @param   $type
     * @return  array
     */
    public function getHookEvent($class, $method, $type)
    {
        $class = isset($this->aliases[$class]) ? $this->aliases[$class] : $class;
        $event = $class . '::' . $method . '::' . $type;

        return $event;
    }

    /**
     * Returns the proxy for the given class. If the Enlight_Hook_ProxyFactory hasn't
     * already instantiated it, the function instantiates it automatically.
     *
     * @param $class
     * @return mixed
     */
    public function getProxy($class)
    {
        return $this->getProxyFactory()->getProxy($class);
    }

    /**
     * Checks if a proxy exists for the given class.
     *
     * @param $class
     * @return bool
     */
    public function hasProxy($class)
    {
        return $this->getProxyFactory()->getProxy($class) !== $class;
    }

    /**
     * Executes all registered hooks for the given hook arguments.
     * First, all hooks of the typeBefore type executed.
     * Then the typeReplace hooks are executed.
     * If no typeReplace hook exists, the function checks if the executeParent method on the subject exists.
     * If this is the case, the executeParent function will be executed.
     * At the end the typeAfter hooks are executed.
     *
     * @param   Enlight_Class|Enlight_Hook_Proxy $class
     * @param   string $method
     * @param   array $args
     * @return  mixed
     */
    public function executeHooks($class, $method, $args)
    {
        $args = new Enlight_Hook_HookArgs(array_merge(array(
            'class' => $class,
            'method' => $method,
        ), $args));
        $className = get_parent_class($class);
        $eventManager = $this->eventManager;

        $event = $this->getHookEvent($className, $method, 'before');
        $eventManager->notify($event, $args);

        $event = $this->getHookEvent($className, $method, 'replace');
        if ($eventManager->hasListeners($event)) {
            $eventManager->notify($event, $args);
        } else {
            $args->setReturn($args->getSubject()->executeParent(
                $method,
                $args->getArgs()
            ));
        }

        $event = $this->getHookEvent($className, $method, 'after');
        return $eventManager->filter($event, $args->getReturn(), $args);
    }

    /**
     * Sets the given name as alias for the given target.
     *
     * @param $name
     * @param $target
     * @return Enlight_Hook_HookManager
     */
    public function setAlias($name, $target)
    {
        $this->aliases[$target] = $name;
        return $this;
    }

    /**
     * Returns the alias for the given name.
     * @param $name
     * @return null
     */
    public function getAlias($name)
    {
        return isset($this->_aliases[$name]) ? $this->_aliases[$name] : null;
    }

    /**
     * Resets the aliases and registered hooks.
     * @return Enlight_Hook_HookManager
     */
    public function resetHooks()
    {
        $this->aliases = array();
        return $this;
    }
}
