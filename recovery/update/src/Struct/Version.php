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

namespace Shopware\Recovery\Update\Struct;

class Version extends Struct
{
    /**
     * @var string Semver compatible version e.G 4.2.1-rc
     */
    public $version;

    /**
     * @var string Type of package eg zip
     */
    public $type;

    /**
     * @var string uri to update package
     */
    public $uri;

    /**
     * @var string sha1 sum of update
     */
    public $sha1;

    /**
     * @var int size in bytes
     */
    public $size;
}
