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

namespace Shopware\Components\Plugin;

use Shopware\Components\Plugin\Configuration\ReaderInterface;
use Shopware\Models\Shop\Shop;

/**
 * @deprecated since 5.7 and removed in 5.9. Use `Shopware\Components\Plugin\Configuration\ReaderInterface` instead
 */
class DBALConfigReader implements ConfigReader
{
    /**
     * @var ReaderInterface
     */
    private $reader;

    public function __construct(ReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param string $pluginName
     *
     * @return array
     *
     * @deprecated since 5.7 and removed in 5.9. Use `Shopware\Components\Plugin\Configuration\ReaderInterface`::getByPluginName instead
     */
    public function getByPluginName($pluginName, Shop $shop = null)
    {
        return $this->reader->getByPluginName($pluginName, $shop === null ? null : $shop->getId());
    }
}
