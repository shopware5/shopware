<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

class Shopware_StoreApi_Exception_Response
{
    const UNKNOWN_EXCEPTION = 100;
    const OUT_OF_RANGE = 200;
    const AUTHORIZATION_FAILED = 300;
    const ACCESS_FORBIDDEN = 301;
    const DOMAIN_ACCESS_FORBIDDEN = 302;
    const PRODUCT_NOT_FOUND = 400;
    const PRODUCT_COULD_NOT_ADDED = 401;
    const NO_RENT_VERSION_AVAILABLE = 402;

    protected $messages = array(
        100  => 'Unknown Exception',
        200  => 'Out of range',
        300  => 'Authorization failed',
        301  => 'Access forbidden',
        302  => 'Domain access forbidden',
        400  => 'Product not found',
        401  => 'Product could not added',
        402  => 'No rent version available'
    );

    private $message;
    private $code;

    public function __construct($message, $code)
    {
        $this->message = $message;
        $this->code = $code;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getCode()
    {
        return $this->code;
    }
}
