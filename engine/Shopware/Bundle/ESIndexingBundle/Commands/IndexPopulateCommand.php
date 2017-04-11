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

namespace Shopware\Bundle\ESIndexingBundle\Commands;

use Shopware\Bundle\ESIndexingBundle\Console\ConsoleProgressHelper;
use Shopware\Bundle\ESIndexingBundle\ShopIndexerInterface;
use Shopware\Bundle\StoreFrontBundle\Shop\Shop;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class IndexPopulateCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:es:index:populate')
            ->setDescription('Reindex all shops into a new index and switch the live-system alias after the index process.')
            ->addOption('shopId', null, InputOption::VALUE_OPTIONAL)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($shopId = $input->getOption('shopId')) {
            $shops = $this->container->get('storefront.shop.gateway')->getList(
                [$shopId],
                new TranslationContext((int) $shopId, true, null)
            );
        } else {
            $shops = $this->container->get('shopware_elastic_search.identifier_selector')->getShops();
        }

        /** @var ShopIndexerInterface $indexer */
        $indexer = $this->container->get('shopware_elastic_search.shop_indexer');

        $helper = new ConsoleProgressHelper($output);

        /** @var Shop $shop */
        foreach ($shops as $shop) {
            $output->writeln("\n## Indexing shop " . $shop->getName() . ' ##');
            $indexer->index($shop, $helper);
        }
    }
}
