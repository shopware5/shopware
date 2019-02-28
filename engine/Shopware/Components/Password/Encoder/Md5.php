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

class Md5 implements PasswordEncoderInterface
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Md5';
    }

    /**
     * @param string $password
     * @param string $hash
     *
     * @return bool
     */
    public function isPasswordValid($password, $hash)
    {
        if (strpos($hash, ':') === false) {
            return hash_equals($hash, md5($password));
        }
        list($md5, $salt) = explode(':', $hash);

        return hash_equals($md5, md5($password . $salt));
    }

    /**
     * @param string $password
     *
     * @return string
     */
    public function encodePassword($password)
    {
        return md5($password);
    }

    /**
     * @param string $hash
     *
     * @return bool
     */
    public function isReencodeNeeded($hash)
    {
        return false;
    }
}
