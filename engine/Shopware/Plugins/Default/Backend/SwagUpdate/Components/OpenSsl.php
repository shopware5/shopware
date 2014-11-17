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

namespace ShopwarePlugins\SwagUpdate\Components;

/**
 * @category  Shopware
 * @package   ShopwarePlugins\SwagUpdate\Components;
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class OpenSsl
{
    /**
     * @var null
     */
    private $privateKeyResource ;

    /**
     * @var string
     */
    private $privateKey;

    /**
     * @var string
     */
    private $publicKey;

    /**
     * @var array
     */
    private $keyArgs = array(
        'digest_alg'       => 'sha512',
        'private_key_bits' => 4096,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    );

    /**
     * @param  string     $password
     * @param  array      $keyArgs
     * @throws \Exception
     */
    public function createKeys($password = null, $keyArgs = null)
    {
        if (isset($keyArgs)) {
            $keyArgs = array_merge_recursive($this->keyArgs, $keyArgs);
        } else {
            $keyArgs = $this->keyArgs;
        }

        if (false === $this->privateKeyResource = openssl_pkey_new($keyArgs)) {
            while ($errors[] = openssl_error_string());
            throw new \Exception(sprintf("Error during key creation: \n%s", implode("\n", $errors)));
        }

        if (false === openssl_pkey_export($this->privateKeyResource, $this->privateKey, $password)) {
            while ($errors[] = openssl_error_string());
            throw new \Exception(sprintf("Error during key export: \n%s", implode("\n", $errors)));
        }

        openssl_pkey_export($this->privateKeyResource, $this->privateKey, $password);
        $this->publicKey = $this->extractPublicKey($this->privateKeyResource);
    }

    /**
     * @param  string            $file
     * @throws \RuntimeException
     */
    public function exportPrivateKey($file)
    {
        if (!isset($this->privateKey)) {
            throw new \RuntimeException('A private key is not yet known to this class.');
        }

        file_put_contents($file, $this->privateKey, LOCK_EX);
    }

    /**
     * @param $file
     * @param  null              $passphrase
     * @throws \RuntimeException
     */
    public function importPrivateKey($file, $passphrase = null)
    {
        if (!file_exists($file) || !is_readable($file)) {
            throw new \RuntimeException('The private key file cannot be found/read: ' . $file);
        }
        $key = file_get_contents($file);
        $this->setPrivateKey($key, $passphrase);
    }

    /**
     * @param $file
     * @throws \RuntimeException
     */
    public function exportPublicKey($file)
    {
        if (!isset($this->publicKey)) {
            throw new \RuntimeException('A public key is not yet known to this class.');
        }
        file_put_contents($file, $this->publicKey, LOCK_EX);
    }

    /**
     * @param $file
     * @throws \RuntimeException
     */
    public function importPublicKey($file)
    {
        if (!file_exists($file) || !is_readable($file)) {
            throw new \RuntimeException('The public key file cannot be found/read: ' . $file);
        }
        $this->publicKey = file_get_contents($file);
    }

    /**
     * @return null
     */
    public function getPrivateKeyResource()
    {
        return $this->privateKeyResource;
    }

    /**
     * @param  string            $privateKey
     * @param  string            $passphrase
     * @throws \RuntimeException
     */
    public function setPrivateKey($privateKey, $passphrase = null)
    {
        if (false === $this->privateKeyResource = openssl_pkey_get_private($privateKey, $passphrase)) {
            while ($errors[] = openssl_error_string());
            throw new \RuntimeException(sprintf("Could not import private key: \n%s", implode("\n", $errors)));
        }
        $this->privateKey = $privateKey;
        $this->publicKey = $this->extractPublicKey($this->privateKeyResource);
    }

    /**
     * @return mixed
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @param $publicKey
     * @throws \RuntimeException
     */
    public function setPublicKey($publicKey)
    {
        if (false === openssl_pkey_get_public($publicKey)) {
            while ($errors[] = openssl_error_string());
            throw new \RuntimeException(sprintf("Error during public key read: \n%s", implode("\n", $errors)));
        }

        $this->publicKey = $publicKey;
    }

    /**
     * @return mixed
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * Sign a message
     *
     * @param string $message
     *
     * @throws \RuntimeException
     * @return string
     */
    public function sign($message)
    {
        $signature = '';
        if (false === openssl_sign($message, $signature, $this->privateKeyResource)) {
            while ($errors[] = openssl_error_string());
            throw new \RuntimeException(sprintf("Error during private key read: \n%s", implode("\n", $errors)));
        }

        return base64_encode($signature);
    }

    /**
     * @param $message
     * @param $signature
     * @return bool
     * @throws \RuntimeException
     */
    public function verify($message, $signature)
    {
        $signature = base64_decode($signature);

        // state whether signature is okay or not
        $ok = openssl_verify($message, $signature, $this->publicKey);
        if ($ok == 1) {
            return true;
        } elseif ($ok == 0) {
            return false;
        } else {
            while ($errors[] = openssl_error_string());
            throw new \RuntimeException(sprintf("Error during private key read: \n%s", implode("\n", $errors)));
        }
    }

    /**
     * @param $privateKeyResource
     * @return string
     */
    private function extractPublicKey($privateKeyResource)
    {
        $keys = openssl_pkey_get_details($privateKeyResource);

        return $keys['key'];
    }
}
