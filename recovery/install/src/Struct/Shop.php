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

class Shop extends Struct
{
    /**
     * Name of the shop e.g. "My example shop"
     *
     * @var string
     */
    public $name;

    /**
     * Shop owner email address
     *
     * @var string
     */
    public $email;

    /**
     * Shop host including port
     * e.g.
     * "localhost:8080"
     * "my-example.com"
     *
     * @var string
     */
    public $host;

    /**
     * Base path to shop if installed in a sub directory
     * Leave blank if installed in root dir
     *
     * @var string
     */
    public $basePath;

    /**
     * Default shop locale e.g. "en_GB"
     *
     * @var string
     */
    public $locale;

    /**
     * Default shopware currency e.g. "EUR"
     *
     * @var string
     */
    public $currency = 'EUR';
}
