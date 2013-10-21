<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

namespace Shopware\Components\DependencyInjection\Bridge;

use Shopware\Components\Model\Configuration;

/**
 * @category  Shopware
 * @package   Shopware\Components\DependencyInjection\Bridge
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class ModelConfig
{
    /**
     * @var array
     */
    protected $option;

    /**
     * Current instance of the application cache layer.
     *
     * @var \Zend_Cache_Core
     */
    protected $cache;

    /**
     * Instance of the application hook manager.
     * Used to make the doctrine repositories hookable.
     *
     * @var \Enlight_Hook_HookManager
     */
    protected $hookManager;

    public function __construct($option, \Zend_Cache_Core $cache, \Enlight_Hook_HookManager $hookManager)
    {
        $this->option = $option;
        $this->cache = $cache;
        $this->hookManager = $hookManager;
    }

    public function factory()
    {
        $config = new Configuration(
            $this->option
        );

        if ($config->getMetadataCacheImpl() === null) {
            $cacheResource = $this->cache;
            $config->setCacheResource($cacheResource);
        }

        $config->setHookManager($this->hookManager);

        return $config;
    }
}
