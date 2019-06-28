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

use Shopware\Components\Random;

/**
 * Provides a salted + streched sha256
 */
class Sha256 implements PasswordEncoderInterface
{
    /**
     * @var array
     */
    protected $options = [
        'iterations' => 1000,
        'salt_len' => 22,
    ];

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
     * @return string
     */
    public function getName()
    {
        return 'Sha256';
    }

    /**
     * @param string $password
     * @param string $hash
     *
     * @return bool
     */
    public function isPasswordValid($password, $hash)
    {
        list($iterations, $salt) = explode(':', $hash);

        $verifyHash = $this->generateInternal($password, $salt, (int) $iterations);

        return hash_equals($hash, $verifyHash);
    }

    /**
     * @param string $password
     *
     * @return string
     */
    public function encodePassword($password)
    {
        /** @var int $interations */
        $iterations = $this->options['iterations'];
        $salt = $this->getSalt();

        return $this->generateInternal($password, $salt, $iterations);
    }

    /**
     * @param string $hash
     *
     * @return bool
     */
    public function isReencodeNeeded($hash)
    {
        /* @var int $interations */
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
     * Generate a salt using the number generator
     *
     * @return string
     */
    public function getSalt()
    {
        return Random::getAlphanumericString($this->options['salt_len']);
    }

    /**
     * @param string $password
     * @param string $salt
     * @param int    $iterations
     *
     * @return string
     */
    protected function generateInternal($password, $salt, $iterations)
    {
        $hash = '';
        for ($i = 0; $i <= $iterations; ++$i) {
            $hash = hash('sha256', $hash . $password . $salt);
        }

        return $iterations . ':' . $salt . ':' . $hash;
    }
}
