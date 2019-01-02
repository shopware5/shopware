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

namespace Shopware\Bundle\PluginInstallerBundle\Context;

class UpdateListingRequest extends BaseRequest
{
    /**
     * @var string
     */
    private $domain;

    /**
     * @var array
     */
    private $plugins;

    /**
     * @param string $domain
     * @param string $locale
     * @param array  $plugins
     * @param string $shopwareVersion
     */
    public function __construct(
        $locale,
        $shopwareVersion,
        $domain,
        $plugins
    ) {
        $this->domain = $domain;
        $this->plugins = $plugins;

        parent::__construct($locale, $shopwareVersion);
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return array
     */
    public function getPlugins()
    {
        return $this->plugins;
    }
}
