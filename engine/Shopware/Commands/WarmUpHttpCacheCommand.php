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

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WarmUpHttpCacheCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:warm:http:cache')
            ->setDescription('Warm up http cache')
            ->addArgument('shopId', InputArgument::OPTIONAL, 'The Id of the shop')
            ->addOption('clear-cache', 'c', InputOption::VALUE_NONE, 'Clear complete httpcache before warmup')
            ->setHelp('The <info>%command.name%</info> warms up the http cache')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shopId = $input->getArgument('shopId');

        if (!empty($shopId)) {
            $shopIds[] = $shopId;
        } else {
            $shopIds = $this->container->get('db')->fetchCol('SELECT id FROM s_core_shops WHERE active = 1');
        }

        if ($input->getOption('clear-cache')) {
            $output->writeln('Clearing httpcache.');
            $this->container->get('shopware.cache_manager')->clearHttpCache();
        }

        /** @var \Shopware\Components\HttpCache\CacheWarmer $cacheWarmer */
        $cacheWarmer = $this->container->get('http_cache_warmer');

        foreach ($shopIds as $shopId) {
            $limit = 10;
            $offset = 0;
            $totalUrlCount = $cacheWarmer->getAllSEOUrlCount($shopId);
            $output->writeln("\n Calling URLs for shop with id " . $shopId);
            $progressBar = new ProgressBar($output, $totalUrlCount);
            $progressBar->setBarWidth(100);
            $progressBar->start();
            while ($offset < $totalUrlCount) {
                $urls = $cacheWarmer->getAllSEOUrls($shopId, $limit, $offset);

                $cacheWarmer->callUrls($urls, $shopId);
                $progressBar->advance(count($urls));
                $offset += count($urls);
            }
            $progressBar->finish();
        }

        $output->writeln("\n The HttpCache is now warmed up");
    }
}
