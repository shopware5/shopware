<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components\Password\Encoder;

use DomainException;
use Shopware\Components\Random;

/**
 * Provides a salted + streched sha256
 */
class Sha256 implements PasswordEncoderInterface
{
    private const DELIMITER = ':';

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
        if ($options === null) {
            return;
        }

        if (!isset($options['iterations']) || ($options['iterations'] > 1000000 || $options['iterations'] < 1)) {
            $options['iterations'] = 100000;
        }

        $this->options = $options;
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
        if (!str_contains($hash, self::DELIMITER)) {
            throw new DomainException(sprintf('Invalid hash provided for the encoder %s.', $this->getName()));
        }

        list($iterations, $salt) = explode(self::DELIMITER, $hash);

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
        /** @var int $iterations */
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
        list($iterations, $salt) = explode(':', $hash);

        if ($iterations != $this->options['iterations']) {
            return true;
        }

        if (\strlen($salt) != $this->options['salt_len']) {
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

        if (!\is_string($hash)) {
            throw new DomainException(sprintf('The password could not be hashed by %s.', $this->getName()));
        }

        return $iterations . ':' . $salt . ':' . $hash;
    }
}
