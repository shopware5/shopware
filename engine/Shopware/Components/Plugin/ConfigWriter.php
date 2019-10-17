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

use Shopware\Components\Plugin\Configuration\WriterInterface;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Shop;

/**
 * @deprecated since 5.6 and removed in 5.8. Use `Shopware\Components\Plugin\Configuration\WriterInterface` instead
 */
class ConfigWriter
{
    /**
     * @var WriterInterface
     */
    private $writer;

    public function __construct(WriterInterface $writer)
    {
        $this->writer = $writer;
    }

    /**
     * @param array $elements
     *
     * @deprecated Use since 5.6 and removed in 5.8. `Shopware\Components\Plugin\Configuration\WriterInterface`::setByPluginName instead
     */
    public function savePluginConfig(Plugin $plugin, $elements, Shop $shop)
    {
        $this->writer->setByPluginName($plugin->getName(), $elements, $shop->getId());
    }

    /**
     * @param string $name
     *
     * @throws \Exception
     *
     * @deprecated Use since 5.6 and removed in 5.8. `Shopware\Components\Plugin\Configuration\WriterInterface`::setByPluginName instead
     */
    public function saveConfigElement(Plugin $plugin, $name, $value, Shop $shop)
    {
        $this->writer->setByPluginName(
            $plugin->getName(),
            [$name => $value],
            $shop->getId()
        );
    }
}
