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

namespace Shopware\Components\License\Struct;

use RuntimeException;

class ShopwareEdition
{
    public const CE = 'CE'; // Community Edition
    public const PE = 'PE'; // Professional - SwagCore
    public const PP = 'PP'; // Professional - SwagCorePlus
    public const EE = 'EE'; // Enterprise - SwagEnterprisePlatform
    public const EB = 'EB'; // (legacy) Enterprise Basic - SwagEnterprise
    public const EC = 'EC'; // (legacy) Enterprise Premium - SwagEnterprisePremium/SwagEnterpriseCluster

    /**
     * @var string
     */
    public $edition;

    /**
     * @var string
     */
    public $licence;

    /**
     * @param string $edition
     * @param string $licence
     *
     * @throws RuntimeException
     */
    private function __construct($edition, $licence = null)
    {
        $edition = strtoupper($edition);
        $this->edition = $edition;
        $this->licence = $licence;
    }

    /**
     * @return bool
     */
    public function isCommercial()
    {
        return $this->edition != self::CE;
    }

    /**
     * Returns a list of valid commercial product keys
     *
     * @return array
     */
    public static function getValidEditions()
    {
        return [
            self::PE,
            self::PP,
            self::EE,
            self::EB,
            self::EC,
        ];
    }

    /**
     * @param string $edition
     * @param string $licence
     *
     * @return ShopwareEdition
     */
    public static function createFromEditionAndLicence($edition, $licence)
    {
        return new self($edition, $licence);
    }
}
