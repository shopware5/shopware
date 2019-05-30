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

namespace Shopware\Bundle\SitemapBundle\Commands;

use Shopware\Bundle\SitemapBundle\Exception\AlreadyLockedException;
use Shopware\Commands\ShopwareCommand;
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
                'Generate sitemap only for for this shop')
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
        /** @var \Shopware\Models\Shop\Repository $repository */
        $repository = $this->container->get('models')->getRepository(Shop::class);

        $shops = null;
        $shopId = $input->getOption('shopId');

        if ($shopId) {
            /** @var Shop|null $shop */
            $shop = $repository->getById((int) $shopId);
            if ($shop) {
                $shops = [$shop];
            } else {
                throw new \RuntimeException(sprintf('Could not found a shop with id %d', $shopId));
            }
        }

        if (empty($shops)) {
            $shops = $repository->getActiveShopsFixed();
        }

        $sitemapExporter = $this->container->get('shopware_bundle_sitemap.service.sitemap_exporter');
        foreach ($shops as $shop) {
            $output->writeln(sprintf('Generating sitemaps for shop #%d (%s)...', $shop->getId(), $shop->getName()));

            if ($input->getOption('force')) {
                $this->container
                    ->get('shopware_bundle_sitemap.service.sitemap_lock')
                    ->unLock($shop);
            }

            try {
                $sitemapExporter->generate($shop);
            } catch (AlreadyLockedException $exception) {
                $output->writeln(sprintf('ERROR: %s', $exception->getMessage()));
            }
        }

        $output->writeln('done!');
    }
}
