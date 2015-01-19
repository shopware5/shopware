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
class ShopwareEdition
{
    const CE = 'CE';
    const PE = 'PE';
    const EB = 'EB';
    const EC = 'EC';

    /**
     * @var string[]
     */
    private $validEditions = [
        self::CE,
        self::PE,
        self::EB,
        self::EC,
    ];

    /**
     * @var string
     */
    public $edition;

    /**
     * @var string
     */
    public $licence;

    /**
     * @param  string            $edition
     * @param  string            $licence
     * @throws \RuntimeException
     */
    private function __construct($edition, $licence = null)
    {
        $edition = strtoupper($edition);
        if (!in_array($edition, $this->validEditions)) {
            throw new \RuntimeException(
                sprintf(
                    'Edition must be one of %s, given: %s.',
                    implode(", ", $this->validEditions),
                    (string) $edition
                )
            );
        }

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
     * @param $edition
     * @param $licence
     * @return ShopwareEdition
     */
    public static function createFromEditionAndLicence($edition, $licence)
    {
        return new self($edition, $licence);
    }
}
