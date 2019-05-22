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
 * Enlight component for the authentication.
 *
 * The Enlight_Components_Auth is an extension of the Zend_Auth. It extends the zend authentication with an standard
 * adapter and refresh the authentication over the session id.
 *
 * @category   Enlight
 * @package    Enlight_Auth
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Auth extends Zend_Auth
{
    /**
     * @var Zend_Auth_Adapter_Interface
     */
    protected $_adapter;

    /**
     * Returns the persistent storage handler
     *
     * @return Zend_Auth_Adapter_Interface
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * Sets the persistent storage handler
     *
     * @param Zend_Auth_Adapter_Interface $adapter
     * @return Enlight_Components_Auth
     */
    public function setAdapter(Zend_Auth_Adapter_Interface $adapter)
    {
        $this->_adapter = $adapter;
        return $this;
    }

    /**
     * Returns the identity from storage or null if no identity is available
     *
     * @param null|Zend_Auth_Adapter_Interface $adapter
     * @return Zend_Auth_Result
     */
    public function authenticate(Zend_Auth_Adapter_Interface $adapter = null)
    {
        if ($adapter == null) {
            $adapter = $this->_adapter;
        }
        $result = parent::authenticate($adapter);

        if ($result->isValid() && method_exists($adapter, 'getResultRowObject')) {
            $user = $adapter->getResultRowObject();
            $this->getStorage()->write($user);
        } else {
            $this->getStorage()->clear();
        }

        return $result;
    }

    /**
     * Refreshes the auth object
     *
     * @param null|Zend_Auth_Adapter_Interface $adapter
     * @return Zend_Auth_Result
     */
    public function refresh(Zend_Auth_Adapter_Interface $adapter = null)
    {
        if ($adapter == null) {
            $adapter = $this->_adapter;
        }

        $result = $adapter->refresh();

        if (!$result->isValid()) {
            $this->getStorage()->clear();
        }

        return $result;
    }

    /**
     * Returns an instance of Enlight_Components_Auth
     *
     * @static
     * @return Enlight_Components_Auth
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}
