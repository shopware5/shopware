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

use Shopware\Models\ProductFeed\ProductFeed;
use Shopware\Models\ProductFeed\Repository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateProductFeedCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:product:feeds:refresh')
            ->setDescription('Refreshes product feed cache files.')
            ->setHelp('The <info>%command.name%</info> refreshes the cached product feed files.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cacheDir = $this->container->getParameter('kernel.cache_dir');
        $cacheDir .= '/productexport/';

        if (!is_dir($cacheDir)) {
            if (false === @mkdir($cacheDir, 0777, true)) {
                throw new \RuntimeException(sprintf("Unable to create the %s directory (%s)\n", 'Productexport', $cacheDir));
            }
        } elseif (!is_writable($cacheDir)) {
            throw new \RuntimeException(sprintf("Unable to write in the %s directory (%s)\n", 'Productexport', $cacheDir));
        }

        /** @var $export \sExport */
        $export = $this->container->get('modules')->Export();
        $export->sSYSTEM = $this->container->get('system');

        $sSmarty = $this->container->get('template');

        // prevent notices to clutter generated files
        $this->registerErrorHandler($output);

        /** @var Repository $productFeedRepository */
        $productFeedRepository = $this->container->get('models')->getRepository(ProductFeed::class);
        $activeFeeds = $productFeedRepository->getActiveListQuery()->getResult();

        /** @var $feedModel ProductFeed */
        foreach ($activeFeeds as $feedModel) {
            if ($feedModel->getInterval() == 0) {
                continue;
            }
            $output->writeln(sprintf('Refreshing cache for ' . $feedModel->getName()));

            $export->sFeedID = $feedModel->getId();
            $export->sHash = $feedModel->getHash();
            $export->sInitSettings();
            $export->sSmarty = clone $sSmarty;
            $export->sInitSmarty();

            $fileName = $feedModel->getHash() . '_' . $feedModel->getFileName();

            $feedCachePath = $cacheDir . '/' . $fileName;
            $handleResource = fopen($feedCachePath, 'w');
            $export->executeExport($handleResource);
        }

        $output->writeln(sprintf('Product feed cache successfully refreshed'));
    }
}
