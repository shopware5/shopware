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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateProductFeedCommand extends ShopwareCommand
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Enlight_Template_Manager
     */
    private $sSmarty;

    /**
     * @var CacheDir
     */
    private $cacheDir;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:product:feeds:refresh')
            ->setDescription('Refreshes product feed cache files.')
            ->addOption(
                'feedid',
                null,
                InputOption::VALUE_OPTIONAL,
                'ID of the feed to generate'
            )
            ->setHelp('The <info>%command.name%</info> refreshes the cached product feed files.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->cacheDir = $this->container->getParameter('kernel.cache_dir');
        $this->cacheDir .= '/productexport/';

        if (!is_dir($this->cacheDir)) {
            if (false === @mkdir($this->cacheDir, 0777, true)) {
                throw new \RuntimeException(sprintf("Unable to create the %s directory (%s)\n", 'Productexport', $this->cacheDir));
            }
        } elseif (!is_writable($this->cacheDir)) {
            throw new \RuntimeException(sprintf("Unable to write in the %s directory (%s)\n", 'Productexport', $this->cacheDir));
        }

        $feedID = (int) $input->getOption('feedid');

        /** @var $export \sExport */
        $export = $this->container->get('modules')->Export();
        $export->sSYSTEM = $this->container->get('system');

        $this->sSmarty = $this->container->get('template');

        // prevent notices to clutter generated files
        $this->registerErrorHandler($output);

        /** @var Repository $productFeedRepository */
        $productFeedRepository = $this->container->get('models')->getRepository(ProductFeed::class);
        if(empty($feedID)) {
          $activeFeeds = $productFeedRepository->getActiveListQuery()->getResult();

          /** @var $feedModel ProductFeed */
          foreach ($activeFeeds as $feedModel) {
              if ($feedModel->getInterval() == 0) {
                  continue;
              }
              $this->generate_feed($export, $feedModel);
          }
        } else {
          $productFeed = $productFeedRepository->find((int) $feedID);
          if(!empty($productFeed)) {
            $this->generate_feed($export, $productFeed);
          } else {
            throw new \RuntimeException(sprintf("Unable to load feed with id ()%s)\n", $feedID));
          }
        }

        $this->output->writeln(sprintf('Product feed cache successfully refreshed'));
    }

    private function generate_feed($export, ProductFeed $feedModel)
    {
      $this->output->writeln(sprintf('Refreshing cache for ' . $feedModel->getName()));

      $export->sFeedID = $feedModel->getId();
      $export->sHash = $feedModel->getHash();
      $export->sInitSettings();
      $export->sSmarty = clone $this->sSmarty;
      $export->sInitSmarty();

      $fileName = $feedModel->getHash() . '_' . $feedModel->getFileName();

      $feedCachePath = $this->cacheDir . '/' . $fileName;
      $handleResource = fopen($feedCachePath, 'w');
      $export->executeExport($handleResource);
    }
}
