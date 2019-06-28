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
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Commands\ShopwareCommand;
use Shopware\Models\Shop\Repository;
use Shopware\Models\Shop\Shop as ShopModel;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IndexPopulateCommand extends ShopwareCommand implements CompletionAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        if ($optionName === 'shopId') {
            /** @var Repository $shopRepository */
            $shopRepository = $this->getContainer()->get('models')->getRepository(ShopModel::class);
            $queryBuilder = $shopRepository->createQueryBuilder('shop');

            if (is_numeric($context->getCurrentWord())) {
                $queryBuilder->andWhere($queryBuilder->expr()->like('shop.id', ':id'))
                    ->setParameter('id', addcslashes($context->getCurrentWord(), '%_') . '%');
            }

            $result = $queryBuilder->select(['shop.id'])
                ->addOrderBy($queryBuilder->expr()->asc('shop.id'))
                ->getQuery()
                ->getArrayResult();

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
            ->setName('sw:es:index:populate')
            ->setDescription('Reindex all shops into a new index and switch the live-system alias after the index process.')
            ->addOption('shopId', null, InputOption::VALUE_OPTIONAL, 'The shop to populate')
            ->addOption('index', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'The index to populate')
            ->addOption('no-evaluation', null, InputOption::VALUE_NONE, 'Disable evaluation for each index')
            ->addOption('stop-on-error', null, InputOption::VALUE_NONE, 'Abort indexing if an error occurs')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($shopId = $input->getOption('shopId')) {
            $shops = [$this->container->get('shopware_storefront.shop_gateway_dbal')->get($shopId)];
        } else {
            $shops = $this->container->get('shopware_elastic_search.identifier_selector')->getShops();
        }

        /** @var ShopIndexerInterface $indexer */
        $indexer = $this->container->get('shopware_elastic_search.shop_indexer');

        $helper = new ConsoleProgressHelper($output);

        $evaluation = $this->container->get('shopware_elastic_search.console.console_evaluation_helper');
        $evaluation->setOutput($output)
            ->setActive(!$input->getOption('no-evaluation'))
            ->setStopOnError($input->getOption('stop-on-error'));

        /** @var Shop $shop */
        foreach ($shops as $shop) {
            $output->writeln("\n## Indexing shop " . $shop->getName() . ' ##');
            $indexer->index($shop, $helper, $input->getOption('index'));
        }
    }
}
