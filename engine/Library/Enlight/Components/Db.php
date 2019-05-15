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
 * Enlight component to get database access.
 *
 * Class for connecting to SQL databases and performing common operations.
 * The factory function of the Enlight_Components_Db class creates
 * a new instance of the database component. The factory expects the
 * adapter name which will used for the database operations.
 *
 * @category   Enlight
 * @package    Enlight_Db
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Db extends Zend_Db
{
    /**
     * Namespace of the adapter (default : Enlight_Components_Db_Adapter)
     * @var string
     */
    protected static $_adapterNamespace = 'Enlight_Components_Db_Adapter';

    /**
     * Factory for Db-Adapter classes.
     *
     * First argument may be a string containing the base of the adapter class
     * name, e.g. 'Mysqli' corresponds to class Zend_Db_Adapter_Mysqli.  This
     * name is currently case-insensitive, but is not ideal to rely on this behavior.
     * If your class is named 'My_Company_Pdo_Mysql', where 'My_Company' is the namespace
     * and 'Pdo_Mysql' is the adapter name, it is best to use the name exactly as it
     * is defined in the class.  This will ensure proper use of the factory API.
     *
     * First argument may alternatively be an object of type Zend_Config.
     * The adapter class base name is read from the 'adapter' property.
     * The adapter config parameters are read from the 'params' property.
     *
     * Second argument is optional and may be an associative array of key-value
     * pairs.  This is used as the argument to the adapter constructor.
     *
     * If the first argument is of type Zend_Config, it is assumed to contain
     * all parameters, and the second argument is ignored.
     *
     * @param  mixed $adapter String name of base adapter class, or Zend_Config object.
     * @param  mixed $config  OPTIONAL; an array or Zend_Config object with adapter parameters.
     * @return Zend_Db_Adapter_Abstract
     * @throws Zend_Db_Exception
     */
    public static function factory($adapter, $config = array())
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }
        /*
         * Convert Zend_Config argument to plain string
         * adapter name and separate config object.
         */
        if ($adapter instanceof Zend_Config) {
            if (isset($adapter->params)) {
                $config = $adapter->params->toArray();
            }
            if (isset($adapter->adapter)) {
                $adapter = (string) $adapter->adapter;
            } else {
                $adapter = null;
            }
        }

        $adapterName = str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($adapter))));

        if (empty($config['adapterNamespace']) && class_exists(self::$_adapterNamespace . '_' . $adapterName)) {
            $config['adapterNamespace'] = self::$_adapterNamespace;
        }

        return parent::factory($adapter, $config);
    }
}
