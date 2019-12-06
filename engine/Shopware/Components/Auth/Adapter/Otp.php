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

namespace Shopware\Components\Auth\Adapter;

use DateInterval;
use DateTime;
use Shopware_Components_Auth_Adapter_Default;
use Throwable;
use Zend_Auth_Result;

class Otp extends Shopware_Components_Auth_Adapter_Default
{
    public function authenticate()
    {
        // set one-time-password as column to hold the password-hash
        $this->setCredentialColumn('otp');

        // execute default login routine
        $result = parent::authenticate();

        try {
            if ($this->_resultRow['otp_activation'] === null) {
                // the otp has not been activated
                $result = new Zend_Auth_Result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, $result->getIdentity());
            } else {
                $now = new DateTime();
                $validUntil = (new DateTime($this->_resultRow['otp_activation']))->add(new DateInterval('PT30S'));

                if ($now > $validUntil) {
                    // the otp has expired
                    $result = new Zend_Auth_Result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, $result->getIdentity());
                }
            }

            if ($result->getCode() === Zend_Auth_Result::SUCCESS) {
                // reset otp hash and activation if one login was successful
                $this->_zendDb->update($this->_tableName, [
                    'otp' => null,
                    'otp_activation' => null,
                ], sprintf('%s = "%s"', $this->_identityColumn, $result->getIdentity()));
            }
        } catch (Throwable $exception) {
            return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, $result->getIdentity());
        }

        return $result;
    }
}
