<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
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
