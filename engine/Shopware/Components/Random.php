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

/**
 * Pseudorandom number generator (PRNG).
 *
 * This class is highly based on Rand.php of Component_ZendMath
 *
 *
 * @see      https://github.com/zendframework/zf2/blob/master/library/Zend/Math/Rand.php
 * @see      https://github.com/ircmaxell/RandomLib
 *
 * @license   http://opensource.org/licenses/bsd-license.php New BSD License
 */
abstract class Random
{
    /**
     * Generate random bytes
     *
     * @param int $length
     *
     * @return string|false
     */
    public static function getBytes($length)
    {
        if ($length <= 0) {
            return false;
        }

        return random_bytes($length);
    }

    /**
     * Generate random boolean
     *
     * @return bool
     */
    public static function getBoolean()
    {
        $byte = static::getBytes(1);

        return (bool) (ord($byte) % 2);
    }

    /**
     * Generate a random integer between $min and $max inclusive
     *
     * @param int $min
     * @param int $max
     *
     * @throws \DomainException
     *
     * @return int
     */
    public static function getInteger($min, $max)
    {
        if ($min > $max) {
            throw new \DomainException('The min parameter must be lower than max parameter');
        }

        return random_int($min, $max);
    }

    /**
     * Generate random float (0..1)
     * This function generates floats with platform-dependent precision
     *
     * @return float
     */
    public static function getFloat()
    {
        $bytes = static::getBytes(7);
        $bytes[6] = $bytes[6] | chr(0xF0);
        $bytes .= chr(63); // exponent bias (1023)
        list(, $float) = unpack('d', $bytes);

        return $float - 1;
    }

    /**
     * Generate a random string of specified length.
     *
     * Uses supplied character list for generating the new string.
     * If no character list provided - uses Base 64 character set.
     *
     * @param int         $length
     * @param string|null $charlist
     *
     * @throws \DomainException
     *
     * @return string
     */
    public static function getString($length, $charlist = null)
    {
        if ($length < 1) {
            throw new \DomainException('Length should be >= 1');
        }

        // Charlist is empty or not provided
        if (empty($charlist)) {
            $numBytes = ceil($length * 0.75);
            $bytes = static::getBytes($numBytes);

            return mb_substr(rtrim(base64_encode($bytes), '='), 0, $length, '8bit');
        }

        $listLen = mb_strlen($charlist, '8bit');

        if ($listLen === 1) {
            return str_repeat($charlist, $length);
        }

        $result = '';
        for ($i = 0; $i < $length; ++$i) {
            $pos = static::getInteger(0, $listLen - 1);
            $result .= $charlist[$pos];
        }

        return $result;
    }

    /**
     * Generate a random alphanumeric string of specified length.
     *
     * Charlist: a-zA-Z0-9
     *
     * @param int $length
     *
     * @throws \DomainException
     *
     * @return string
     */
    public static function getAlphanumericString($length)
    {
        if ($length < 1) {
            throw new \DomainException('Length should be >= 1');
        }

        $charlist = implode(range('a', 'z')) . implode(range('A', 'Z')) . implode(range(0, 9));

        return static::getString($length, $charlist);
    }

    /**
     * @see https://gist.github.com/tylerhall/521810
     * Generates a strong password of N length containing at least one lower case letter,
     * one uppercase letter, one digit, and one special character. The remaining characters
     * in the password are chosen at random from those four sets.
     *
     * The available characters in each set are user friendly - there are no ambiguous
     * characters such as i, l, 1, o, 0, etc. This makes it much easier for users to manually
     * type or speak their passwords.
     *
     * @param int   $length
     * @param array $availableSets
     *
     * @return string
     */
    public static function generatePassword($length = 15, $availableSets = ['l', 'u', 'd', 's'])
    {
        $sets = [];
        if (in_array('l', $availableSets, true)) {
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        }
        if (in_array('u', $availableSets, true)) {
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        }
        if (in_array('d', $availableSets, true)) {
            $sets[] = '23456789';
        }
        if (in_array('s', $availableSets, true)) {
            $sets[] = '!@#$%&*?';
        }

        $pool = '';
        $password = '';

        foreach ($sets as $set) {
            $password .= self::getRandomArrayElement(str_split($set));
            $pool .= $set;
        }

        $pool = str_split($pool);
        for ($i = 0; $i < $length - count($sets); ++$i) {
            $password .= self::getRandomArrayElement($pool);
        }
        $password = str_shuffle($password);

        return $password;
    }

    /**
     * Return a random element from an array
     *
     * @param array $array
     */
    public static function getRandomArrayElement($array)
    {
        return $array[self::getInteger(0, count($array) - 1)];
    }
}
