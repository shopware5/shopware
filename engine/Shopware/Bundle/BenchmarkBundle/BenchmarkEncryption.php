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

namespace Shopware\Bundle\BenchmarkBundle;

use Shopware\Components\OpenSSLEncryption;
use Shopware\Components\OpenSSLVerifier;

class BenchmarkEncryption
{
    /**
     * @var OpenSSLEncryption
     */
    private $encryption;

    /**
     * @var OpenSSLVerifier
     */
    private $verifier;

    /**
     * @param string $publicKeyPath
     */
    public function __construct($publicKeyPath)
    {
        $publicKey = trim(file_get_contents($publicKeyPath));

        $this->encryption = new OpenSSLEncryption($publicKey);
        $this->verifier = new OpenSSLVerifier($publicKeyPath);
    }

    /**
     * @param string $data
     * @param string $encryptionMethod
     *
     * @return array
     */
    public function encryptData($data, $encryptionMethod)
    {
        return $this->encryption->encryptData($data, $encryptionMethod);
    }

    /**
     * @param string $encryptionMethod
     *
     * @return bool
     */
    public function isEncryptionSupported($encryptionMethod)
    {
        return $this->encryption->isEncryptionSupported($encryptionMethod);
    }

    /**
     * @return bool
     */
    public function isSignatureSupported()
    {
        return $this->verifier->isSystemSupported();
    }

    /**
     * @param string $message
     * @param string $signature
     *
     * @return bool
     */
    public function isSignatureValid($message, $signature)
    {
        return $this->verifier->isValid($message, $signature);
    }
}
