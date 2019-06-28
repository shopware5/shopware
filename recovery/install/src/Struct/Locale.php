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

namespace Shopware\Recovery\Install\Struct;

class Locale
{
    /**
     * @var string
     */
    private $locale;

    /**
     * @param string $locale
     */
    private function __construct($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->locale;
    }

    /**
     * Returns a list of valid locales
     *
     * @return array
     */
    public static function getValidLocales()
    {
        return [
            'de_DE',
            'en_GB',
        ];
    }

    /**
     * @param string $locale
     *
     * @return Locale
     */
    public static function createFromString($locale)
    {
        return new self($locale);
    }
}
