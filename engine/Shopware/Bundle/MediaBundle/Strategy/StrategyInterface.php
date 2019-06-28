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

namespace Shopware\Bundle\MediaBundle\Strategy;

interface StrategyInterface
{
    /**
     * Cleans the shopware media path
     *
     * Eg. 'http//asdfsadf/asdf/media/image/foobar.png' -> '/media/image/foobar.png'
     *     '/var/www/web1/media/image/foobar.png' -> '/media/image/foobar.png'
     *
     * @param string $path
     *
     * @return string
     */
    public function normalize($path);

    /**
     * Builds the path on the filesystem
     *
     * @param string $path
     *
     * @return string
     */
    public function encode($path);

    /**
     * Checks if the provided path matches the algorithm format
     *
     * @param string $path
     *
     * @return bool
     */
    public function isEncoded($path);
}
