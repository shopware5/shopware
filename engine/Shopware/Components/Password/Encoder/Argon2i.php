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

use DomainException;

class Argon2i implements PasswordEncoderInterface
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param array $options
     */
    public function __construct($options = null)
    {
        if ($options === null) {
            return;
        }

        if (!isset($options['memory_cost']) || $options['memory_cost'] < 256) {
            $options['memory_cost'] = PASSWORD_ARGON2_DEFAULT_MEMORY_COST;
        }

        if (!isset($options['time_cost']) || ($options['time_cost'] < 1 || $options['time_cost'] > 30)) {
            $options['time_cost'] = PASSWORD_ARGON2_DEFAULT_TIME_COST;
        }

        if (!isset($options['threads']) || ($options['threads'] < 1 || $options['threads'] > 32)) {
            $options['threads'] = PASSWORD_ARGON2_DEFAULT_THREADS;
        }

        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Argon2i';
    }

    /**
     * @return bool
     */
    public function isCompatible()
    {
        return \defined('PASSWORD_ARGON2I');
    }

    /**
     * @param string $password
     * @param string $hash
     *
     * @return bool
     */
    public function isPasswordValid($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * @param string $password
     *
     * @return string
     */
    public function encodePassword($password)
    {
        $hash = password_hash($password, PASSWORD_ARGON2I, $this->options);

        if (!\is_string($hash)) {
            throw new DomainException(sprintf('Password could not be encoded by the encoder %s.', PASSWORD_ARGON2I));
        }

        return $hash;
    }

    /**
     * @param string $hash
     *
     * @return bool
     */
    public function isReencodeNeeded($hash)
    {
        return password_needs_rehash($hash, PASSWORD_ARGON2I, $this->options);
    }
}
