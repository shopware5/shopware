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

namespace Shopware\Bundle\SitemapBundle\ConfigHandler;

use DateTime;
use Shopware\Bundle\SitemapBundle\Service\ConfigHandler;

/**
 * @deprecated Will be removed with Shopware 5.8 without replacement. Use `Shopware\Bundle\SitemapBundle\ConfigHandler\Database` instead.
 */
class File implements ConfigHandlerInterface
{
    /**
     * @var array
     */
    private $sitemapConfig;

    public function __construct(array $sitemapConfig)
    {
        $this->sitemapConfig = $sitemapConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getSitemapConfig(): array
    {
        return [
            ConfigHandler::EXCLUDED_URLS_KEY => $this->sitemapConfig['excluded_urls'],
            ConfigHandler::CUSTOM_URLS_KEY => $this->getSitemapCustomUrls($this->sitemapConfig['custom_urls']),
        ];
    }

    public function saveCustomUrls(array $customUrls): void
    {
    }

    public function saveExcludedUrls(array $excludedUrls): void
    {
    }

    /**
     * @return array
     */
    private function getSitemapCustomUrls(array $customUrls)
    {
        foreach ($customUrls as &$customUrl) {
            $customUrl['lastMod'] = DateTime::createFromFormat('Y-m-d H:i:s', $customUrl['lastMod']);
        }

        return $customUrls;
    }
}
