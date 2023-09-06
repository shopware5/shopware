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

namespace Shopware\Bundle\SitemapBundle\Commands;

use Shopware\Bundle\SitemapBundle\Exception\AlreadyLockedException;
use Shopware\Bundle\SitemapBundle\Service\SitemapExporter;
use Shopware\Bundle\SitemapBundle\Service\SitemapLock;
use Shopware\Commands\ShopwareCommand;
use Shopware\Components\Model\Exception\ModelNotFoundException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SitemapGenerateCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:generate:sitemap')
            ->setDescription('Generates sitemaps for a given shop (or all active ones)')
            ->addOption(
                'shopId',
                'i',
                InputOption::VALUE_OPTIONAL,
                'Generate sitemap only for for this shop'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force generation, even if generation has been locked by some other process'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repository = $this->container->get(ModelManager::class)->getRepository(Shop::class);

        $shops = null;
        $shopId = (int) $input->getOption('shopId');

        if ($shopId > 0) {
            $shop = $repository->getById($shopId);
            if ($shop instanceof Shop) {
                $shops = [$shop];
            } else {
                throw new ModelNotFoundException(Shop::class, $shopId);
            }
        }

        if (empty($shops)) {
            $shops = $repository->getActiveShopsFixed();
        }

        $sitemapExporter = $this->container->get(SitemapExporter::class);
        foreach ($shops as $shop) {
            $output->writeln(sprintf('Generating sitemaps for shop #%d (%s)...', $shop->getId(), $shop->getName()));

            if ($input->getOption('force')) {
                $this->container
                    ->get(SitemapLock::class)
                    ->unLock($shop);
            }

            try {
                $sitemapExporter->generate($shop);
            } catch (AlreadyLockedException $exception) {
                $output->writeln(sprintf('ERROR: %s', $exception->getMessage()));
            }
        }

        $output->writeln('Done!');

        return 0;
    }
}
