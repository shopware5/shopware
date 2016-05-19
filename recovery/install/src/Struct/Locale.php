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

/**
 * @category  Shopware
 * @package   Shopware\Recovery\Install\Struct
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Locale
{
    /**
     * @var string
     */
    private $locale;

    /**
     * Returns a list of valid locales
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
     */
    private function __construct($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @param string $locale
     * @return Locale
     */
    public static function createFromString($locale)
    {
        return new self($locale);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->locale;
    }
}
