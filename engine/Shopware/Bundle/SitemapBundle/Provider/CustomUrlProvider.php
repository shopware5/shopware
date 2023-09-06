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

namespace Shopware\Bundle\SitemapBundle\Provider;

use DateTimeImmutable;
use Shopware\Bundle\SitemapBundle\Service\ConfigHandler;
use Shopware\Bundle\SitemapBundle\Struct\Url;
use Shopware\Bundle\SitemapBundle\UrlProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Routing\Context;

class CustomUrlProvider implements UrlProviderInterface
{
    private ConfigHandler $configHandler;

    private bool $allExported = false;

    public function __construct(ConfigHandler $configHandler)
    {
        $this->configHandler = $configHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls(Context $routingContext, ShopContextInterface $shopContext)
    {
        if ($this->allExported) {
            return [];
        }

        $sitemapCustomUrls = $this->configHandler->get(ConfigHandler::CUSTOM_URLS_KEY);

        $urls = [];
        foreach ($sitemapCustomUrls as $sitemapCustomUrl) {
            if (!$this->isAvailableForShop($sitemapCustomUrl, (int) $shopContext->getShop()->getId())) {
                continue;
            }

            $urls[] = new Url(
                $sitemapCustomUrl['url'],
                new DateTimeImmutable($sitemapCustomUrl['lastMod']),
                $sitemapCustomUrl['changeFreq'],
                'custom',
                null,
                $sitemapCustomUrl['priority']
            );
        }

        $this->allExported = true;

        return $urls;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->allExported = false;
    }

    private function isAvailableForShop(array $url, int $shopId): bool
    {
        return \in_array((int) $url['shopId'], [$shopId, 0], true);
    }
}
