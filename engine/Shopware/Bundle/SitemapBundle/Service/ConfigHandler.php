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

namespace Shopware\Bundle\SitemapBundle\Service;

use Shopware\Bundle\SitemapBundle\ConfigHandler\ConfigHandlerInterface;

class ConfigHandler
{
    const EXCLUDED_URLS_KEY = 'excludedUrls';
    const CUSTOM_URLS_KEY = 'customUrls';

    /**
     * @var ConfigHandlerInterface[]
     */
    private $configHandlers;

    public function __construct(\IteratorAggregate $configHandlers)
    {
        $this->configHandlers = iterator_to_array($configHandlers, false);
    }

    /**
     * @param string $key
     *
     * @return array
     */
    public function get($key)
    {
        $filteredUrls = [];
        $customUrls = [];

        foreach ($this->configHandlers as $configHandler) {
            $config = $configHandler->getSitemapConfig();
            $filteredUrls = $this->addUrls($filteredUrls, $config[self::EXCLUDED_URLS_KEY]);
            $customUrls = $this->addUrls($customUrls, $config[self::CUSTOM_URLS_KEY]);
        }

        if ($key === self::EXCLUDED_URLS_KEY) {
            return $filteredUrls;
        }

        if ($key === self::CUSTOM_URLS_KEY) {
            return $customUrls;
        }

        throw new \RuntimeException(sprintf("Invalid sitemap config key: '%s'", $key));
    }

    /**
     * @return array
     */
    private function addUrls(array $urls, array $config)
    {
        foreach ($config as $configUrl) {
            $urls[] = $configUrl;
        }

        return $urls;
    }
}
