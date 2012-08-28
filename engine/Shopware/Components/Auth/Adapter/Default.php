<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Components_Auth
 * @subpackage Adapter
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Shopware default auth adapter
 *
 * todo@all: Class-description
 *
 * <code>
 * $authComponent = new Shopware_Components_Auth_Adapter_Default
 * $authComponent->authenticate();
 * </code>
 */
class Shopware_Components_Auth_Adapter_Default extends Enlight_Components_Auth_Adapter_DbTable
{
    /**
     * Table to do authentication against
     * @var string
     */
    protected $_tableName = 's_core_auth';
    /**
     * Column that holds the username
     * @var string
     */
    protected $_identityColumn = 'username';
    /**
     * Column that holds the password
     * @var string
     */
    protected $_credentialColumn = 'password';
    /**
     * Rule set to salt the password
     * @var string
     */
    protected $_credentialTreatment = "MD5(CONCAT('A9ASD:_AD!_=%a8nx0asssblPlasS$',MD5(?)))";
    /**
     * Array with conditions that have to be true in auth request
     * @var array
     */
    protected $conditions = array('active=1', 'lockeduntil <= NOW()');
    /**
     * Column that holds the expire date
     * @var string
     */
    protected $expiryColumn = 'lastlogin';
    /**
     * Column that holds the session id
     * @var string
     */
    protected $sessionIdColumn = 'sessionID';
    /**
     * For bruce force protection - column that holds the date until the login is permitted
     * @var string
     */
    protected $lockedUntilColumn = 'lockeduntil';
    /**
     * How many seconds is a login is valid?
     * @var int
     */
    protected $expiry = 7200;

    /**
     * Set some properties only available at runtime
     */
    public function __construct()
    {
        parent::__construct();
        // Add conditions to user queries
        foreach ($this->conditions as $condition) {
            $this->addCondition($condition);
        }
        $this->setSessionId(Enlight_Components_Session::getId());
    }

    /**
     * authenticate() - defined by Zend_Auth_Adapter_Interface.  This method is called to
     * attempt an authentication.  Previous to this call, this adapter would have already
     * been configured with all necessary information to successfully connect to a database
     * table and attempt to find a record matching the provided identity.
     *
     * @throws Zend_Auth_Adapter_Exception if answering the authentication query is impossible
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $result = parent::authenticate();

        // Get temporary set of user - data
        $zendDbSelect = clone $this->_dbSelect;
        $zendDbSelect->reset();
        $zendDbSelect->from($this->_tableName);
        $zendDbSelect->where('username = ?', $this->_identity);

        $getUserData = $this->_zendDb->fetchRow($zendDbSelect->assemble());

        if ($result->isValid()) {
            // Check if user role is active
            $roleId = $getUserData["roleID"];
            if ($this->_zendDb->fetchOne("SELECT enabled FROM s_core_auth_roles WHERE id = ?", array($roleId)) == false) {
                return false;
            }
            $this->updateExpiry();
            $this->updateSessionId();
        } else {
            $failedLogins = $getUserData["failedlogins"];
            // If more then 4 previous failed logins lock account for n * failedlogins seconds
            if ($failedLogins >= 4) {
                $datetime = new Zend_Date();
                $datetime->addSecond($this->lockSeconds * $failedLogins);
                $this->setLockedUntil($datetime);
            }
            // Increase number of failed logins
            $this->setFailedLogins($failedLogins + 1);
        }
        return $result;
    }

    /**
     * Set the property failed logins to a new value
     * @param $number
     * @return Shopware_Components_Auth_Adapter_Default
     */
    protected function setFailedLogins($number)
    {
        $this->_zendDb->update(
            $this->_tableName,
            array('failedlogins' => $number),
            $this->_zendDb->quoteInto(
                $this->_zendDb->quoteIdentifier($this->_identityColumn, true) . ' = ?', $this->_identity
            )
        );
        return $this;
    }
}