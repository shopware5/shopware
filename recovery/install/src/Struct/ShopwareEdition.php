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

class ShopwareEdition
{
    const CE = 'CE'; // Community Edition
    const PE = 'PE'; // Professional - SwagCore
    const PP = 'PP'; // Professional - SwagCorePlus
    const EE = 'EE'; // Enterprise - SwagEnterprisePlatform
    const EB = 'EB'; // (legacy) Enterprise Basic - SwagEnterprise
    const EC = 'EC'; // (legacy) Enterprise Premium - SwagEnterprisePremium/SwagEnterpriseCluster

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
     * @throws \RuntimeException
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
