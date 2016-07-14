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
 * Shopware Auth component
 */
class Shopware_Components_Auth extends Enlight_Components_Auth
{
    /**
     * List with auth adapters
     * @var array
     */
    protected $_adapter = array();

    /**
     * Adapter that is current active - has a valid user session
     * @var null
     */
    protected $_baseAdapter = null; // Current active adapter

    /**
     * Get all adapters or certain one
     *
     * @param null $index
     * @return array|Zend_Auth_Adapter_Interface
     */
    public function getAdapter($index = null)
    {
        if (isset($index)) {
            return $this->_adapter[$index];
        }
        return $this->_adapter;
    }

    /**
     * Add adapter to list
     * @param Zend_Auth_Adapter_Interface $adapter
     * @return Shopware_Components_Auth
     */
    public function addAdapter(Zend_Auth_Adapter_Interface $adapter)
    {
        $this->_adapter[] = $adapter;
        return $this;
    }

    /**
     * Login method - iterate through all adapters and check for valid account
     *
     * @param string $username
     * @param string $password
     * @return Zend_Auth_Result
     */
    public function login($username, $password)
    {
        $result = null;
        $adapters = $this->getAdapter();
        foreach ($adapters as $adapter) {
            $adapter->setIdentity($username);
            $adapter->setCredential($password);

            $result = $this->authenticate($adapter);
            if ($result->isValid()) {
                $this->setBaseAdapter($adapter);
                return $result;
            }
        }
        $this->setBaseAdapter(null);
        return $result;
    }

    /**
     * Set current active adapter
     *
     * @param $adapter
     * @return \Shopware_Components_Auth
     */
    public function setBaseAdapter($adapter)
    {
        $this->_baseAdapter = $adapter;
        return $this;
    }

    /**
     * Get current active adapter
     *
     * @return Zend_Auth_Adapter_Interface
     */
    public function getBaseAdapter()
    {
        return $this->_baseAdapter;
    }

    /**
     * Do a authentication approve with a defined adapter
     *
     * @param null|Zend_Auth_Adapter_Interface $adapter
     * @return Zend_Auth_Result
     */
    public function authenticate(Zend_Auth_Adapter_Interface $adapter = null)
    {
        if ($adapter == null) {
            $adapter = $this->_baseAdapter;
        }

        $result = parent::authenticate($adapter);

        // If authentication with the current adapter was succeeded, read user data from default adapter (database)
        if ($result->isValid() && method_exists($this->getAdapter(0), 'getResultRowObject')) {
            $user = $this->getAdapter(0)->getResultRowObject();
            $this->getStorage()->write($user);
        } else {
            $this->getStorage()->clear();
        }

        return $result;
    }

    /**
     * Refresh authentication - for example expire date -
     *
     * @param null|Zend_Auth_Adapter_Interface $adapter
     * @return mixed
     */
    public function refresh(Zend_Auth_Adapter_Interface $adapter = null)
    {
        if ($adapter == null) {
            $adapter = $this->getBaseAdapter();
        }
        $result = $adapter->refresh();

        if (!$result->isValid()) {
            $this->getStorage()->clear();
        }

        return $result;
    }

    /**
     * Get an instance from this object
     * @static
     * @return Shopware_Components_Auth
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Validates the given credentials of a user
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function isPasswordValid($username, $password)
    {
        $storage = $this->getStorage();
        $adapters = $this->getAdapter();
        $this->setStorage(new Zend_Auth_Storage_NonPersistent());

        foreach ($adapters as $adapter) {
            $adapter->setIdentity($username);
            $adapter->setCredential($password);

            $result = $this->authenticate($adapter);
            if ($result->isValid()) {
                $this->setStorage($storage);
                return true;
            }
        }

        $this->setStorage($storage);
        return false;
    }
}
