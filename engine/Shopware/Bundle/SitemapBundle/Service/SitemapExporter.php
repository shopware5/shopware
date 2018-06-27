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

use Shopware\Bundle\SitemapBundle\SitemapExporterInterface;
use Shopware\Bundle\SitemapBundle\SitemapWriterInterface;
use Shopware\Bundle\SitemapBundle\UrlProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\ConfigWriter;
use Shopware\Components\Routing\Context;
use Shopware\Models\Shop\Shop;
use Shopware_Components_Config as ShopwareConfig;

class SitemapExporter implements SitemapExporterInterface
{
    /**
     * @var SitemapWriterInterface
     */
    private $sitemapWriter;

    /**
     * @var UrlProviderInterface[]
     */
    private $urlProvider;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var ShopwareConfig
     */
    private $shopwareConfig;

    /**
     * @var ConfigWriter
     */
    private $configWriter;

    /**
     * @param SitemapWriterInterface                   $sitemapWriter
     * @param ContextServiceInterface                  $contextService
     * @param ShopwareConfig                           $shopwareConfig
     * @param \IteratorAggregate<UrlProviderInterface> $urlProvider
     * @param ConfigWriter                             $configWriter
     */
    public function __construct(
        SitemapWriterInterface $sitemapWriter,
        ContextServiceInterface $contextService,
        ShopwareConfig $shopwareConfig,
        \IteratorAggregate $urlProvider,
        ConfigWriter $configWriter
    ) {
        $this->sitemapWriter = $sitemapWriter;
        $this->urlProvider = $urlProvider;
        $this->shopwareConfig = $shopwareConfig;
        $this->contextService = $contextService;
        $this->configWriter = $configWriter;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Shop $shop)
    {
        $routerContext = Context::createFromShop($shop, $this->shopwareConfig);
        $shopContext = $this->contextService->createShopContext($shop->getId(), $shop->getCurrency()->getId(), $shop->getCustomerGroup()->getKey());

        foreach ($this->urlProvider as $urlProvider) {
            $urlProvider->reset();
            while ($urls = $urlProvider->getUrls($routerContext, $shopContext)) {
                $this->sitemapWriter->writeFile($shop, $urls);
            }
        }

        $this->sitemapWriter->closeFiles();

        $this->configWriter->save('sitemapLastRefresh', time());
    }
}
