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

/**
 * Configuration for a single plugin, which loaded over a directory structure.
 *
 * The Enlight_Plugin_Bootstrap_Default is used when plugins registered over directory structures.
 * By registering plugins over a directory structure the plugins have no configuration.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Plugin_Bootstrap_Default extends Enlight_Plugin_Bootstrap
{
    /**
     * The Enlight_Plugin_Bootstrap expects a name for the plugin and
     * optionally an instance of the Enlight_Plugin_PluginCollection
     *
     * @param string                          $name
     * @param Enlight_Plugin_PluginCollection $collection
     */
    public function __construct($name, $collection = null)
    {
        $this->setCollection($collection);
        parent::__construct($name);
    }
}
