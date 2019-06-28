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
 * The Enlight_Class is the basic class for each enlight class.
 *
 * If the class will be instantiated enlight will check if a proxy for the class exists and throw
 * an exception if this is the case.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
abstract class Enlight_Class
{
    /**
     * Contains all initialized Enlight instances.
     *
     * @var array
     */
    protected static $instances = [];

    /**
     * The constructor will prevent the initialization and thrown an Enlight_Exception if the class is flagged
     * as singleton and has already initialed.
     * If an hook proxy exist for the class, the constructor will prevent the initialization and throw an exception
     * that the instance method should be used.
     *
     * @throws \Enlight_Exception
     */
    public function __construct()
    {
        $class = get_class($this);
        if ($this instanceof Enlight_Singleton) {
            if (!isset(self::$instances[$class])) {
                self::$instances[$class] = $this;
            } else {
                throw new Enlight_Exception(
                    'Class "' . get_class($this) . '" is singleton, please use the instance method'
                );
            }
        }
        if ($this instanceof Enlight_Hook
          && !$this instanceof Enlight_Hook_Proxy
          && Shopware()->Hooks()->hasProxy($class)) {
            throw new Enlight_Exception(
                'Class "' . get_class($this) . '" has hooks, please use the instance method'
            );
        }
        if (method_exists($this, 'init')) {
            if (func_num_args()) {
                call_user_func_array([$this, 'init'], func_get_args());
            } else {
                $this->init();
            }
        }
    }

    /**
     * Magic caller
     *
     * @param string $name
     * @param array  $args
     *
     * @throws \Enlight_Exception
     */
    public function __call($name, $args = null)
    {
        throw new Enlight_Exception(
            'Method "' . get_class($this) . '::' . $name . '" not found failure',
            Enlight_Exception::METHOD_NOT_FOUND
        );
    }

    /**
     * Magic static caller
     *
     * @param string $name
     * @param array  $args
     *
     * @throws \Enlight_Exception
     */
    public static function __callStatic($name, $args = null)
    {
        throw new Enlight_Exception(
            'Method "' . get_called_class() . '::' . $name . '" not found failure',
            Enlight_Exception::METHOD_NOT_FOUND
        );
    }

    /**
     * Magic getter
     *
     * @param string $name
     *
     * @throws \Enlight_Exception
     */
    public function __get($name)
    {
        throw new Enlight_Exception('Property "' . $name . '" not found failure', Enlight_Exception::PROPERTY_NOT_FOUND);
    }

    /**
     * Magic setter
     *
     * @param string $name
     * @param mixed  $value
     *
     * @throws \Enlight_Exception
     */
    public function __set($name, $value = null)
    {
        throw new Enlight_Exception('Property "' . $name . '" not found failure', Enlight_Exception::PROPERTY_NOT_FOUND);
    }

    /**
     * Returns the class name of the given class. If no class is given, the class will drawn by
     * get_called_class(). If the given class has an hook proxy the function will return the proxy class.
     *
     * @param mixed $class
     *
     * @throws \Enlight_Exception
     *
     * @return string
     */
    public static function getClassName($class = null)
    {
        if (empty($class)) {
            $class = get_called_class();
        }

        if (is_object($class)) {
            $class = get_class($class);
        } elseif (!class_exists($class)) {
            throw new Enlight_Exception('Class ' . $class . ' does not exist and could not be loaded');
        }

        if (in_array('Enlight_Hook', class_implements($class))) {
            $class = Shopware()->Hooks()->getProxy($class);
        }

        return $class;
    }

    /**
     * Returns a class instance. If the class is already initialed the existing instance will returned.
     * Otherwise the class will be initialed with the given arguments.
     *
     * @param string $class
     * @param array  $args
     *
     * @throws \ReflectionException
     *
     * @return Enlight_Class
     */
    public static function Instance($class = null, $args = null)
    {
        $class = self::getClassName($class);
        if (isset(self::$instances[$class])) {
            return self::$instances[$class];
        }

        $rc = new ReflectionClass($class);

        if (isset($args)) {
            $instance = $rc->newInstanceArgs($args);
        } else {
            $instance = $rc->newInstance();
        }

        return $instance;
    }

    /**
     * Reset the instance of the given class.
     *
     * @param mixed $class
     */
    public static function resetInstance($class = null)
    {
        $class = self::getClassName($class);
        unset(self::$instances[$class]);
    }
}
