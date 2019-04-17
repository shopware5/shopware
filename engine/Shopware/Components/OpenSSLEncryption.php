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

namespace Shopware\Components;

class OpenSSLEncryption
{
    /**
     * @var string
     */
    private $publicKey;

    /**
     * @param string $publicKey
     */
    public function __construct($publicKey)
    {
        $this->publicKey = $publicKey;
    }

    /**
     * @param string $encryptionMethod
     *
     * @return bool
     */
    public function isEncryptionSupported($encryptionMethod)
    {
        if (!extension_loaded('openssl')) {
            return false;
        }

        if (!in_array($encryptionMethod, openssl_get_cipher_methods(true))) {
            return false;
        }

        return true;
    }

    /**
     * @param string $data
     * @param string $encryptionMethod
     *
     * @return array
     */
    public function encryptData($data, $encryptionMethod)
    {
        $publicKeyString = $this->publicKey;

        $publicKey = openssl_pkey_get_public($publicKeyString);

        $key = Random::getAlphanumericString(32);

        $ivLength = openssl_cipher_iv_length($encryptionMethod);
        $iv = Random::getBytes($ivLength);

        $encryptedMessage = openssl_encrypt($data, $encryptionMethod, $key, 0, $iv);

        $encryptedKey = '';
        if (!openssl_public_encrypt($key, $encryptedKey, $publicKey)) {
            $errors = [];
            while ($errors[] = openssl_error_string());
            $errorString = implode("\n", $errors);
            throw new \Exception(sprintf('Got openssl error %s', $errorString));
        }

        $result = [
            'encryptedKey' => base64_encode($encryptedKey),
            'iv' => base64_encode($iv),
            'encryptionMethod' => $encryptionMethod,
            'encryptedMessage' => $encryptedMessage,
        ];

        return $result;
    }
}
