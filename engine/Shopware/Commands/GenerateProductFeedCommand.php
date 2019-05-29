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

use Enlight_Template_Manager;
use sExport;
use Shopware\Models\ProductFeed\ProductFeed;
use Shopware\Models\ProductFeed\Repository;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateProductFeedCommand extends ShopwareCommand implements CompletionAwareInterface
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
     * @var string
     */
    private $cacheDir;

    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        if ($optionName === 'feed-id') {
            /** @var Repository $productFeedRepository */
            $productFeedRepository = $this->container->get('models')->getRepository(ProductFeed::class);
            $queryBuilder = $productFeedRepository->createQueryBuilder('feed');

            if (!empty($context->getCurrentWord())) {
                $queryBuilder->andWhere($queryBuilder->expr()->like('feed.id', ':id'))
                    ->setParameter('id', addcslashes($context->getCurrentWord(), '%_') . '%');
            }

            $result = $queryBuilder->select(['feed.id'])
                ->addOrderBy($queryBuilder->expr()->asc('feed.id'))
                ->getQuery()
                ->getScalarResult();

            return array_column($result, 'id');
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function completeArgumentValues($argumentName, CompletionContext $context)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:product:feeds:refresh')
            ->setDescription('Refreshes product feed cache files.')
            ->addOption(
                'feed-id',
                'i',
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
        $this->cacheDir = $this->container->getParameter('kernel.cache_dir') . '/productexport/';

        if (!is_dir($this->cacheDir)) {
            if (@mkdir($this->cacheDir, 0777, true) === false) {
                throw new \RuntimeException(sprintf("Unable to create directory '%s'\n", $this->cacheDir));
            }
        } elseif (!is_writable($this->cacheDir)) {
            throw new \RuntimeException(sprintf("Unable to write in directory '%s'\n", $this->cacheDir));
        }

        $feedId = (int) $input->getOption('feed-id');

        /** @var sExport $export */
        $export = $this->container->get('modules')->Export();

        $export->sSYSTEM = $this->container->get('system');

        $this->sSmarty = $this->container->get('template');

        // Prevent notices to clutter generated files
        $this->registerErrorHandler($output);

        /** @var Repository $productFeedRepository */
        $productFeedRepository = $this->container->get('models')->getRepository(ProductFeed::class);
        if (empty($feedId)) {
            $activeFeeds = $productFeedRepository->getActiveListQuery()->getResult();

            /** @var ProductFeed $feedModel */
            foreach ($activeFeeds as $feedModel) {
                if ($feedModel->getInterval() === 0) {
                    continue;
                }
                $this->generateFeed($export, $feedModel);
            }
        } else {
            /** @var ProductFeed $productFeed */
            $productFeed = $productFeedRepository->find((int) $feedId);
            if (empty($productFeed)) {
                throw new \RuntimeException(sprintf("Unable to load feed with id %s\n", $feedId));
            } elseif ($productFeed->getActive() !== 1) {
                throw new \RuntimeException(sprintf("The feed with id %s is not active\n", $feedId));
            }
            $this->generateFeed($export, $productFeed);
        }

        $this->output->writeln(sprintf('Product feed cache successfully refreshed'));
    }

    /**
     * @param \sExport $export
     */
    private function generateFeed($export, ProductFeed $feedModel)
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
