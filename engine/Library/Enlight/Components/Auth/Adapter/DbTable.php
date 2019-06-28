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
 * Adapter for the authentication.
 *
 * The Enlight_Components_Auth_Adapter_DbTable is responsible for the validation
 * of the authentication.
 *
 * The following parameters can be given to the constructor when instantiating
 * db: Zend_Database<br>
 * <b>tableName</b>: The name of the table which stores the login information.<br>
 * <b>identityColumn</b>: The name of the table column that has a unique user ID.<br>
 * <b>credentialColumn</b>: The name of the table column which stores the MD5 hash password.<br>
 * <b>credentialTreatment</b>: SQL command to evaluate the influence of the password.  <br>
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Auth_Adapter_DbTable extends Zend_Auth_Adapter_DbTable
{
    /**
     * Name of column which holds the date on how long a account is blocked
     *
     * @var string
     */
    protected $lockedUntilColumn = null;

    /**
     * Date until a account has been disabled caused by a brute force attack
     *
     * @var Zend_Date
     */
    protected $lockedUntil;

    /**
     * How long should an account be blocked after a failed login attempt in seconds
     *
     * @var int
     */
    protected $lockSeconds = 30;

    /**
     * The expiry Column value
     *
     * @var string
     */
    protected $expiryColumn;

    /**
     * The expiry value
     *
     * @var int
     */
    protected $expiry;

    /**
     * The session id value
     *
     * @var string
     */
    protected $sessionId;

    /**
     * The session id column value
     *
     * @var string
     */
    protected $sessionIdColumn;

    /**
     * Adds a where-condition to the db-select.
     *
     * @param string $condition
     *
     * @return Enlight_Components_Auth_Adapter_DbTable
     */
    public function addCondition($condition)
    {
        $this->getDbSelect()->where($condition);

        return $this;
    }

    /**
     * Sets the database field which holds the date until an account has been disabled.
     *
     * @param string $lockedUntilColumn
     *
     * @throws Exception
     *
     * @return Enlight_Components_Auth_Adapter_DbTable
     */
    public function setLockedUntilColumn($lockedUntilColumn)
    {
        $this->lockedUntilColumn = (string) $lockedUntilColumn;

        return $this;
    }

    /**
     * Sets the expiry column method and the expiry time.
     *
     * @param string $expiryColumn
     * @param int    $expiry
     *
     * @return Enlight_Components_Auth_Adapter_DbTable
     */
    public function setExpiryColumn($expiryColumn, $expiry = 3600)
    {
        $this->expiryColumn = (string) $expiryColumn;
        $this->expiry = $expiry;

        return $this;
    }

    /**
     * authenticate() - defined by Zend_Auth_Adapter_Interface.  This method is called to
     * attempt an authentication.  Previous to this call, this adapter would have already
     * been configured with all necessary information to successfully connect to a database
     * table and attempt to find a record matching the provided identity.
     *
     * @throws Zend_Auth_Adapter_Exception if answering the authentication query is impossible
     *
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $result = parent::authenticate();

        if ($result->isValid()) {
            $this->updateExpiry();
            $this->updateSessionId();
        }

        return $result;
    }

    /**
     * Refresh the authentication.
     *
     * Checks the expiry date and the identity.
     *
     * @return Zend_Auth_Result
     */
    public function refresh()
    {
        $credential = $this->_credential;
        $credentialColumn = $this->_credentialColumn;
        $identity = $this->_identity;
        $identityColumn = $this->_identityColumn;
        $credentialTreatment = $this->_credentialTreatment;

        $expiry = Zend_Date::now()->subSecond($this->expiry);
        $this->setCredential($expiry);
        $this->setCredentialColumn($this->expiryColumn);

        $this->setIdentity($this->sessionId);
        $this->setIdentityColumn($this->sessionIdColumn);

        $result = parent::authenticate();

        $this->_credential = $credential;
        $this->_credentialColumn = $credentialColumn;
        $this->_identity = $identity;
        $this->_identityColumn = $identityColumn;
        $this->_credentialTreatment = $credentialTreatment;

        if ($result->isValid()) {
            $this->updateExpiry();
        }

        return $result;
    }

    /**
     * Sets the session id column value.
     *
     * @param string $sessionIdColumn
     *
     * @return Enlight_Components_Auth_Adapter_DbTable
     */
    public function setSessionIdColumn($sessionIdColumn)
    {
        $this->sessionIdColumn = $sessionIdColumn;

        return $this;
    }

    /**
     * Sets the session id value in the instance.
     *
     * @param string $value
     *
     * @return Enlight_Components_Auth_Adapter_DbTable
     */
    public function setSessionId($value)
    {
        $this->sessionId = $value;

        return $this;
    }

    /**
     * Disables an account until a given date.
     * $date has to be an Zend_Date object
     *
     * @param Zend_Date $date
     *
     * @return Enlight_Components_Auth_Adapter_DbTable
     */
    public function setLockedUntil(Zend_Date $date)
    {
        $this->lockedUntil = $date;
        $this->updateLockUntilDate($date);
        $this->addCondition($this->_zendDb->quoteInto(
            $this->_zendDb->quoteIdentifier($this->lockedUntilColumn, true) . ' <= ?', Zend_Date::now()
        ));

        return $this;
    }

    /**
     * Gets the date until an account has been disabled. Returns a Zend_Date
     *
     * @return Zend_Date
     */
    public function getLockedUntil()
    {
        return $this->lockedUntil;
    }

    /**
     * Defines how long (in seconds) a user has to wait until he is allowed to enter a new password again.
     *
     * @param int $lockSeconds
     *
     * @return Enlight_Components_Auth_Adapter_DbTable
     */
    public function setLockSeconds($lockSeconds)
    {
        $this->lockSeconds = $lockSeconds;

        return $this;
    }

    /**
     * Returns the amount of seconds a user has to wait until he is allowed retry a login attempt.
     *
     * @return int
     */
    public function getLockSeconds()
    {
        return $this->lockSeconds;
    }

    /**
     * Updates the expiration date to now.
     */
    protected function updateExpiry()
    {
        if ($this->expiryColumn === null) {
            return;
        }

        $this->_zendDb->update(
            $this->_tableName,
            [$this->expiryColumn => Zend_Date::now()],
            $this->_zendDb->quoteInto(
                $this->_zendDb->quoteIdentifier($this->_identityColumn, true) . ' = ?', $this->_identity
            )
        );
    }

    /**
     * Update the session id field in the session db.
     *
     * @return Enlight_Components_Auth_Adapter_DbTable
     */
    protected function updateSessionId()
    {
        if ($this->sessionId === null) {
            return $this;
        }
        $this->_zendDb->update(
            $this->_tableName,
            [$this->sessionIdColumn => $this->sessionId],
            $this->_zendDb->quoteInto(
                $this->_zendDb->quoteIdentifier($this->_identityColumn, true) . ' = ?', $this->_identity
            )
        );
        $this->_zendDb->update(
            $this->_tableName,
            [$this->sessionIdColumn => null],
            $this->_zendDb->quoteInto(
                $this->_zendDb->quoteIdentifier($this->_identityColumn, true) . ' != ?', $this->_identity
            )
            . ' AND ' .
            $this->_zendDb->quoteInto(
                $this->_zendDb->quoteIdentifier($this->sessionIdColumn, true) . ' = ?', $this->sessionId
            )
        );

        return $this;
    }

    /**
     * Updates the date until an account has been disabled.
     * $date has to be an MySQL Datetime format yyyy-mm-dd hh:mm:ss
     *
     * @param Zend_Date $date
     *
     * @throws Exception
     *
     * @return Enlight_Components_Auth_Adapter_DbTable
     */
    protected function updateLockUntilDate(Zend_Date $date)
    {
        if ($this->lockedUntilColumn === null) {
            return $this;
        }
        $this->_zendDb->update(
            $this->_tableName,
            [$this->lockedUntilColumn => $date],
            $this->_zendDb->quoteInto(
                $this->_zendDb->quoteIdentifier($this->_identityColumn, true) . ' = ?', $this->_identity
            )
        );

        return $this;
    }
}
