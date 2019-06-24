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

namespace Shopware\Commands;

use Shopware\Components\HttpCache\CacheWarmer;
use Shopware\Components\HttpCache\UrlProvider\UrlProviderInterface;
use Shopware\Components\HttpCache\UrlProviderFactoryInterface;
use Shopware\Components\Routing\Context;
use Shopware\Models\Shop\Shop;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class WarmUpHttpCacheCommand extends ShopwareCommand implements CompletionAwareInterface
{
    /**
     * List of UrlProvider-names that Shopware provides itself
     *
     * @var string[]
     */
    private $defaultProviderNames = ['blog', 'category', 'emotion', 'manufacturer', 'product', 'productwithcategory', 'productwithnumber', 'static', 'variantswitch'];

    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function completeArgumentValues($argumentName, CompletionContext $context)
    {
        if ($argumentName === 'shopId') {
            return $this->completeShopIds($context->getCurrentWord());
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:warm:http:cache')
            ->setDescription('Warm up http cache (everything by default)')
            /* @deprecated since 5.6, to be removed in 6.0 */
            ->addArgument('shopId', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'The Id of the shop (deprecated)')
            ->addOption('shopId', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The Id of the shop (multiple Ids -> shopId={1,2})')
            ->addOption('clear-cache', 'c', InputOption::VALUE_NONE, 'Clear complete httpcache before warmup')
            ->addOption('concurrent-requests', 'b', InputOption::VALUE_OPTIONAL, 'Integer representing the maximum number of requests that are allowed to be sent concurrently. To many URLs at a time may cause script timeouts, memory issues or block your HTTP server', 1)
            ->addOption('category', 'k', InputOption::VALUE_NONE, 'Warm up categories')
            ->addOption('emotion', 'o', InputOption::VALUE_NONE, 'Warm up emotions')
            ->addOption('blog', 'g', InputOption::VALUE_NONE, 'Warm up blogs')
            ->addOption('manufacturer', 'm', InputOption::VALUE_NONE, 'Warm up manufacturers')
            ->addOption('static', 't', InputOption::VALUE_NONE, 'Warm up static pages')
            ->addOption('product', 'p', InputOption::VALUE_NONE, 'Warm up products')
            ->addOption('variantswitch', 'd', InputOption::VALUE_NONE, 'Warm up variant switch of configurators')
            ->addOption('productwithnumber', 'z', InputOption::VALUE_NONE, 'Warm up products and variants with number parameter')
            ->addOption('productwithcategory', 'y', InputOption::VALUE_NONE, 'Warm up products with category parameter')
            ->addOption('extensions', 'x', InputOption::VALUE_NONE, 'Warm up all URLs provided by other extensions')
            ->setHelp('The <info>%command.name%</info> warms up the http cache')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var CacheWarmer $cacheWarmer */
        $cacheWarmer = $this->container->get('http_cache_warmer');

        /** @var UrlProviderFactoryInterface $urlProviderFactory */
        $urlProviderFactory = $this->container->get('shopware_cache_warmer.url_provider_factory');

        $shopIds = null;

        if ($input->getArgument('shopId')) {
            $io = new SymfonyStyle($input, $output);
            $io->warning('Argument "shopId" will be replaced by option "--shopId" in the next major Version');
            $shopIds = $input->getArgument('shopId');
        } elseif ($input->getOption('shopId')) {
            $shopIds = $input->getOption('shopId');
        }

        /** @var \Shopware\Models\Shop\Repository $shopRepository */
        $shopRepository = $this->container->get('models')->getRepository(Shop::class);
        $shops = null;

        if (!empty($shopIds)) {
            foreach ($shopIds as $shopId) {
                $shop = $shopRepository->getById($shopId);

                if (!$shop) {
                    throw new \RuntimeException(sprintf('Shop with id %d not found', $shopId));
                }

                $shops[] = $shop;
            }
        } else {
            $shops = $shopRepository->getActiveShopsFixed();
        }

        $io = new SymfonyStyle($input, $output);
        $options = $this->prepareOptions($input->getOptions(), $urlProviderFactory);

        // Clear cache?
        if ($input->getOption('clear-cache')) {
            $io->writeln('Clearing httpcache.');
            $this->container->get('shopware.cache_manager')->clearHttpCache();
        }

        /*
         * Print information about concurrent requests
         * Help message for this command may be confusing about using an equal sign. So better strip it.
         */
        $concurrentRequests = (int) trim($input->getOption('concurrent-requests'), '=');
        $limit = $concurrentRequests > 10 ? $concurrentRequests : 10;
        $io->writeln(sprintf('Calling URLs with %d concurrent requests', $concurrentRequests));

        // Print warming information
        if (!in_array(false, $options, true)) {
            $io->write('Standard warmup - Warming every url type');
        } else {
            $optionsKeys = array_keys($options, function ($setting) {
                return $setting;
            });
            $optionsKeys = array_map('ucfirst', $optionsKeys);

            $io->write('Specific warmup - Warming only the following url types: ' . implode(', ', $optionsKeys));
        }
        $io->newLine();

        /** @var Shop $shop */
        foreach ($shops as $shop) {
            /** @var Context $context */
            $context = Context::createFromShop(
                $shop,
                $this->container->get('config')
            );

            // Gathering URLs
            $urls = [];
            $totalResultCount = 0;
            $offset = 0;
            foreach ($options as $resource => $active) {
                if ($active) {
                    $provider = $urlProviderFactory->getProvider($resource);
                    $urls = array_merge($urls, $provider->getUrls($context));
                    $totalResultCount += $provider->getCount($context);
                }
            }

            // Progressbar
            $progressBar = $io->createProgressBar($totalResultCount);
            $io->writeln(sprintf("\nShop '%s' (ID: %s)", $shop->getName(), $shop->getId()));
            $progressBar->setBarWidth(100);
            $progressBar->setFormat('very_verbose');
            $progressBar->start();

            // Warm URL-List
            while ($offset < $totalResultCount) {
                $sliceUrls = array_slice($urls, $offset, $limit, true);
                $cacheWarmer->warmUpUrls($sliceUrls, $context, $concurrentRequests);

                $sliceCount = count($sliceUrls);
                if ($sliceCount === 0) {
                    break;
                }
                $progressBar->advance($sliceCount);
                $offset += $sliceCount;
            }
            $progressBar->finish();
            $io->newLine();
        }
        $io->newLine();
        $io->success('The HttpCache is now warmed up');
    }

    /**
     * Builds an array using input parameters, which is used to know what to warm up
     *
     * @return array
     */
    private function prepareOptions(array $input, UrlProviderFactoryInterface $factory)
    {
        $options = [];
        $extensions = [];
        /** @var UrlProviderInterface $provider */
        foreach ($factory->getAllProviders() as $provider) {
            $providerName = $provider->getName();

            if (in_array($providerName, $this->defaultProviderNames, true)) {
                $options[$providerName] = $input[$providerName];
            } else {
                $extensions[$providerName] = true;
            }
        }

        if ($input['extensions'] === true) {
            $options = array_merge($options, $extensions);
        }

        return (in_array(true, $options, true) || $input['extensions'] === true) ? $options : array_map(function () {
            return true;
        }, array_merge($options, $extensions));
    }
}
