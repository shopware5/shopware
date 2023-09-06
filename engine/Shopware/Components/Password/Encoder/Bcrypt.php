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

class Bcrypt implements PasswordEncoderInterface
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

        if (!isset($options['cost']) || ($options['cost'] <= 3 || $options['cost'] >= 32)) {
            $options['cost'] = 10;
        }

        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Bcrypt';
    }

    /**
     * @return bool
     */
    public function isCompatible()
    {
        return PHP_VERSION_ID >= 50307;
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
        $hash = password_hash($password, PASSWORD_BCRYPT, $this->options);

        if (!\is_string($hash)) {
            throw new DomainException(sprintf('Password could not be encoded by the encoder %s.', $this->getName()));
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
        return password_needs_rehash($hash, PASSWORD_BCRYPT, $this->options);
    }
}
