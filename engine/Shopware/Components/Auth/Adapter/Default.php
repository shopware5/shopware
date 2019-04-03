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
 * Shopware default auth adapter
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
     *
     * @var string
     */
    protected $_tableName = 's_core_auth';

    /**
     * Column that holds the username
     *
     * @var string
     */
    protected $_identityColumn = 'username';

    /**
     * Column that holds the password
     *
     * @var string
     */
    protected $_credentialColumn = 'password';

    /**
     * Array with conditions that have to be true in auth request
     *
     * @var array
     */
    protected $conditions = ['active=1', 'lockeduntil <= NOW()'];

    /**
     * Column that holds the expire date
     *
     * @var string
     */
    protected $expiryColumn = 'lastlogin';

    /**
     * Column that holds the session id
     *
     * @var string
     */
    protected $sessionIdColumn = 'sessionID';

    /**
     * For bruce force protection - column that holds the date until the login is permitted
     *
     * @var string
     */
    protected $lockedUntilColumn = 'lockeduntil';

    /**
     * How many seconds is a login is valid?
     *
     * @var int
     */
    protected $expiry = 21600;

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
     *
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $result = parent::authenticate();

        $select = $this->_zendDb->select();
        $select->from($this->_tableName);
        $select->where($this->_zendDb->quoteIdentifier($this->_identityColumn, true) . ' = ?', $this->_identity);
        $user = $this->_zendDb->fetchRow($select, [], Zend_Db::FETCH_OBJ);

        if ($result->isValid()) {
            // Check if user role is active
            $sql = 'SELECT enabled FROM s_core_auth_roles WHERE id = ?';
            if ($this->_zendDb->fetchOne($sql, [$user->roleID]) == false) {
                return new Zend_Auth_Result(
                    Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND,
                    $this->_identity, []
                );
            }

            Enlight_Components_Session::regenerateId();

            // Close and restart session to make sure the db session handler writes updates.
            session_write_close();
            session_start();

            $this->setSessionId(Enlight_Components_Session::getId());

            $this->updateExpiry();
            $this->updateSessionId();

            // Reset failed login count
            $this->setFailedLogins(0);
        } else {
            // If more then 4 previous failed logins lock account for n * failedlogins seconds
            if ($user->failedlogins >= 4) {
                $lockedUntil = new Zend_Date();
                $lockedUntil->addSecond($this->lockSeconds * $user->failedlogins);
                $this->setLockedUntil($lockedUntil);
            }
            // Increase number of failed logins
            $this->setFailedLogins($user->failedlogins + 1);
            if (isset($lockedUntil)) {
                return new Zend_Auth_Result(
                    -4,
                    $this->_identity,
                    ['lockedUntil' => $lockedUntil]
                );
            }
        }

        return $result;
    }

    /**
     * @deprecated in 5.6, will be private in 5.7
     *
     * @param string $plaintext
     * @param string $hash
     * @param string $encoderName
     */
    public function rehash($plaintext, $hash, $encoderName)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.7.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $newHash = Shopware()->PasswordEncoder()->reencodePassword($plaintext, $hash, $encoderName);

        if ($newHash === $hash) {
            return;
        }

        $this->_zendDb->update(
            $this->_tableName,
            [$this->_credentialColumn => $newHash],
            $this->_zendDb->quoteInto(
                $this->_zendDb->quoteIdentifier($this->_identityColumn, true) . ' = ?', $this->_identity
            )
        );
    }

    /**
     * Used for updating to new algorithm for the future
     *
     * @param string $plaintext
     * @param string $defaultEncoderName
     */
    public function updateHash($plaintext, $defaultEncoderName)
    {
        $newHash = Shopware()->PasswordEncoder()->encodePassword($plaintext, $defaultEncoderName);

        $this->_zendDb->update(
            $this->_tableName,
            ['encoder' => $defaultEncoderName, $this->_credentialColumn => $newHash],
            $this->_zendDb->quoteInto(
                $this->_zendDb->quoteIdentifier($this->_identityColumn, true) . ' = ?', $this->_identity
            )
        );
    }

    protected function updateExpiry()
    {
        if ($this->expiryColumn === null) {
            return;
        }

        $user = $this->getResultRowObject();

        $this->_zendDb->update(
           $this->_tableName,
           [$this->expiryColumn => Zend_Date::now()],
           $this->_zendDb->quoteInto(
               $this->_zendDb->quoteIdentifier($this->_identityColumn, true) . ' = ?', $user->username
           )
       );
    }

    /**
     * Set the property failed logins to a new value
     *
     * @param int $number
     *
     * @return Shopware_Components_Auth_Adapter_Default
     */
    protected function setFailedLogins($number)
    {
        $this->_zendDb->update(
            $this->_tableName,
            ['failedlogins' => $number],
            $this->_zendDb->quoteInto(
                $this->_zendDb->quoteIdentifier($this->_identityColumn, true) . ' = ?', $this->_identity
            )
        );

        return $this;
    }

    /**
     * _authenticateCreateSelect() - This method creates a Zend_Db_Select object that
     * is completely configured to be queried against the database.
     *
     * @return Zend_Db_Select
     */
    protected function _authenticateCreateSelect()
    {
        // get select
        $dbSelect = clone $this->getDbSelect();
        $dbSelect->from($this->_tableName, ['*'])
                ->where($this->_zendDb->quoteIdentifier($this->_identityColumn, true) . ' = ?', $this->_identity);

        return $dbSelect;
    }

    /**
     * _authenticateValidateResult() - This method attempts to validate that
     * the record in the resultset is indeed a record that matched the
     * identity provided to this adapter.
     *
     * @param array $resultIdentity
     *
     * @return Zend_Auth_Result
     */
    protected function _authenticateValidateResult($resultIdentity)
    {
        if ($this->_credentialColumn == $this->expiryColumn) {
            if ($this->_credential->toString('YYYY-MM-dd HH:mm:ss') >= $resultIdentity[$this->_credentialColumn]) {
                $passwordValid = false;
            } else {
                $passwordValid = true;
            }
        } else {
            $encoderName = $resultIdentity['encoder'];
            $plaintext = $this->_credential;
            $hash = $resultIdentity[$this->_credentialColumn];

            $passwordValid = Shopware()->PasswordEncoder()->isPasswordValid($plaintext, $hash, $encoderName);
            if ($passwordValid) {
                $defaultEncoderName = Shopware()->PasswordEncoder()->getDefaultPasswordEncoderName();

                if ($encoderName !== $defaultEncoderName) {
                    $this->updateHash($plaintext, $defaultEncoderName);
                } else {
                    $this->rehash($plaintext, $hash, $encoderName);
                }
            }
        }

        if (!$passwordValid) {
            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            $this->_authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';

            return $this->_authenticateCreateAuthResult();
        }

        $this->_resultRow = $resultIdentity;

        $this->_authenticateResultInfo['code'] = Zend_Auth_Result::SUCCESS;
        $this->_authenticateResultInfo['messages'][] = 'Authentication successful.';

        return $this->_authenticateCreateAuthResult();
    }
}
