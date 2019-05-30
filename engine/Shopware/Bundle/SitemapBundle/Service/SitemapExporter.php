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

use Shopware\Bundle\SitemapBundle\Exception\AlreadyLockedException;
use Shopware\Bundle\SitemapBundle\SitemapExporterInterface;
use Shopware\Bundle\SitemapBundle\SitemapLockInterface;
use Shopware\Bundle\SitemapBundle\SitemapWriterInterface;
use Shopware\Bundle\SitemapBundle\UrlFilterInterface;
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
     * @var SitemapLockInterface
     */
    private $sitemapLock;

    /**
     * @var UrlFilterInterface
     */
    private $urlFilter;

    /**
     * @param \IteratorAggregate<UrlProviderInterface> $urlProvider
     */
    public function __construct(
        SitemapWriterInterface $sitemapWriter,
        ContextServiceInterface $contextService,
        ShopwareConfig $shopwareConfig,
        \IteratorAggregate $urlProvider,
        ConfigWriter $configWriter,
        SitemapLockInterface $sitemapLock,
        UrlFilterInterface $urlFilter
    ) {
        $this->sitemapWriter = $sitemapWriter;
        $this->urlProvider = iterator_to_array($urlProvider, false);
        $this->shopwareConfig = $shopwareConfig;
        $this->contextService = $contextService;
        $this->configWriter = $configWriter;
        $this->sitemapLock = $sitemapLock;
        $this->urlFilter = $urlFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Shop $shop)
    {
        if (!$this->sitemapLock->doLock($shop, $this->shopwareConfig->get('sitemapRefreshTime'))) {
            throw new AlreadyLockedException(sprintf('Cannot acquire lock for shop %d', $shop->getId()));
        }

        $routerContext = Context::createFromShop($shop, $this->shopwareConfig);
        $shopContext = $this->contextService->createShopContext($shop->getId(), $shop->getCurrency()->getId(), $shop->getCustomerGroup()->getKey());

        foreach ($this->urlProvider as $urlProvider) {
            $urlProvider->reset();
            while ($urls = $urlProvider->getUrls($routerContext, $shopContext)) {
                $urls = $this->urlFilter->filter($urls, (int) $shop->getId());

                if (!$urls) {
                    continue;
                }

                $this->sitemapWriter->writeFile($shop, $urls);
            }
        }

        $this->sitemapWriter->closeFiles();

        $this->configWriter->save('sitemapLastRefresh', time());

        $this->sitemapLock->unLock($shop);
    }
}
