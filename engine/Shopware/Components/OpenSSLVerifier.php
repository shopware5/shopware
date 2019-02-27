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

class OpenSSLVerifier
{
    /**
     * @var string
     */
    private $publicKeyPath;

    /**
     * @var resource|null
     */
    private $keyResource;

    /**
     * @param string $publicKey
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($publicKey)
    {
        if (!is_readable($publicKey)) {
            throw new \InvalidArgumentException(sprintf('Public keyfile "%s" not readable', $publicKey));
        }

        $this->publicKeyPath = $publicKey;
    }

    /**
     * @return bool
     */
    public function isSystemSupported()
    {
        return function_exists('openssl_verify');
    }

    /**
     * @param string $message
     * @param string $signature
     *
     * @throws \RuntimeException
     *
     * @return bool
     */
    public function isValid($message, $signature)
    {
        $pubkeyid = $this->getKeyResource();

        $signature = base64_decode($signature);

        // State whether signature is okay or not
        $ok = openssl_verify($message, $signature, $pubkeyid);

        if ($ok === 1) {
            return true;
        }
        if ($ok === 0) {
            return false;
        }
        while ($errors[] = openssl_error_string()) {
        }
        throw new \RuntimeException(sprintf("Error during private key read: \n%s", implode("\n", $errors)));
    }

    /**
     * @return resource
     */
    private function getKeyResource()
    {
        if ($this->keyResource) {
            return $this->keyResource;
        }

        $publicKey = trim(file_get_contents($this->publicKeyPath));

        if (false === $this->keyResource = openssl_pkey_get_public($publicKey)) {
            while ($errors[] = openssl_error_string()) {
            }
            throw new \RuntimeException(sprintf("Error during public key read: \n%s", implode("\n", $errors)));
        }

        return $this->keyResource;
    }
}
