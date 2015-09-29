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

namespace Shopware\Components\Password\Encoder;

/**
 * Provides a salted + streched sha256
 *
 * @category  Shopware
 * @package   Shopware\Components\Password\Encoder
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Sha256 implements PasswordEncoderInterface
{
    /**
     * @var array
     */
    protected $options = array(
        'iterations' => 1000,
        'salt_len' => 22
    );

    /**
     * @return string
     */
    public function getName()
    {
        return 'Sha256';
    }

    /**
     * @param array $options
     */
    public function __construct($options = null)
    {
        if ($options !== null) {
            $this->options = $options;
        }
    }

    /**
     * @param  string $password
     * @param  string $hash
     * @return bool
     */
    public function isPasswordValid($password, $hash)
    {
        list($iterations, $salt) = explode(':', $hash);

        $verifyHash = $this->generateInternal($password, $salt, $iterations);

        return hash_equals($hash, $verifyHash);
    }

    /**
     * @param  string $password
     * @return string
     */
    public function encodePassword($password)
    {
        $iterations = $this->options['iterations'];
        $salt       = $this->getSalt();

        return $this->generateInternal($password, $salt, $iterations);
    }

    /**
     * @param  string  $password
     * @param  string  $salt
     * @param  integer $iterations
     * @return string
     */
    protected function generateInternal($password, $salt, $iterations)
    {
        $hash = '';
        for ($i = 0; $i <= $iterations; $i++) {
            $hash = hash('sha256', $hash . $password . $salt);
        }

        return $iterations . ':' . $salt . ':' . $hash;
    }

    /**
     * @param  string $hash
     * @return bool
     */
    public function isReencodeNeeded($hash)
    {
        list($iterations, $salt) = explode(':', $hash);

        if ($iterations != $this->options['iterations']) {
            return true;
        }

        if (strlen($salt) != $this->options['salt_len']) {
            return true;
        }

        return false;
    }

    /**
     * Generate a salt using the best number generator available
     * @return string
     */
    public function getSalt()
    {
        // todo@all replace with \Shopware\Componenents\Random::getBytes()
        $required_salt_len = $this->options['salt_len'];

        $buffer = '';
        $raw_length = (int) ($required_salt_len * 3 / 4 + 1);
        $buffer_valid = false;
        if (function_exists('mcrypt_create_iv') && !defined('PHALANGER')) {
            $buffer = mcrypt_create_iv($raw_length, MCRYPT_DEV_URANDOM);
            if ($buffer) {
                $buffer_valid = true;
            }
        }
        if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
            $buffer = openssl_random_pseudo_bytes($raw_length);
            if ($buffer) {
                $buffer_valid = true;
            }
        }
        if (!$buffer_valid && is_readable('/dev/urandom')) {
            $f = fopen('/dev/urandom', 'r');
            $read = strlen($buffer);
            while ($read < $raw_length) {
                $buffer .= fread($f, $raw_length - $read);
                $read = strlen($buffer);
            }
            fclose($f);
            if ($read >= $raw_length) {
                $buffer_valid = true;
            }
        }
        if (!$buffer_valid || strlen($buffer) < $raw_length) {
            $bl = strlen($buffer);
            for ($i = 0; $i < $raw_length; $i++) {
                if ($i < $bl) {
                    $buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
                } else {
                    $buffer .= chr(mt_rand(0, 255));
                }
            }
        }
        $salt = str_replace('+', '.', base64_encode($buffer));

        return substr($salt, 0, $required_salt_len);
    }
}
