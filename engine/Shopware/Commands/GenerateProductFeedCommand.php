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

namespace Shopware\Commands;

use Enlight_Template_Manager;
use RuntimeException;
use sExport;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\ProductFeed\ProductFeed;
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
            $productFeedRepository = $this->container->get(ModelManager::class)->getRepository(ProductFeed::class);
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
        $this->cacheDir = $this->container->getParameter('shopware.product_export.cache_dir');

        if (!\is_string($this->cacheDir)) {
            throw new RuntimeException('Parameter shopware.product_export.cache_dir has to be a string');
        }

        if (!is_dir($this->cacheDir)) {
            if (@mkdir($this->cacheDir, 0777, true) === false) {
                throw new RuntimeException(sprintf("Unable to create directory '%s'\n", $this->cacheDir));
            }
        } elseif (!is_writable($this->cacheDir)) {
            throw new RuntimeException(sprintf("Unable to write in directory '%s'\n", $this->cacheDir));
        }

        $feedId = (int) $input->getOption('feed-id');

        $export = $this->container->get('modules')->Export();

        $export->sSYSTEM = $this->container->get('modules')->System();

        $this->sSmarty = $this->container->get(Enlight_Template_Manager::class);

        // Prevent notices to clutter generated files
        $this->registerErrorHandler($output);

        $productFeedRepository = $this->container->get(ModelManager::class)->getRepository(ProductFeed::class);
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
            /** @var ProductFeed|null $productFeed */
            $productFeed = $productFeedRepository->find((int) $feedId);
            if ($productFeed === null) {
                throw new RuntimeException(sprintf("Unable to load feed with id %s\n", $feedId));
            }

            if ($productFeed->getActive() !== 1) {
                throw new RuntimeException(sprintf("The feed with id %s is not active\n", $feedId));
            }
            $this->generateFeed($export, $productFeed);
        }

        $this->output->writeln(sprintf('Product feed cache successfully refreshed'));

        return 0;
    }

    /**
     * @param sExport $export
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

        if (!\is_resource($handleResource)) {
            throw new RuntimeException(sprintf('Feed cache path %s can not be opened', $feedCachePath));
        }

        $export->executeExport($handleResource);
    }
}
