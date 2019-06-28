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

use Elasticsearch\Client;
use Shopware\Bundle\ESIndexingBundle\Struct\ShopIndex;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Commands\ShopwareCommand;
use Shopware\Models\Shop\Repository;
use Shopware\Models\Shop\Shop as ShopModel;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SwitchAliasCommand extends ShopwareCommand implements CompletionAwareInterface
{
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

        if ($argumentName === 'index') {
            /** @var Client $client */
            $client = $this->container->get('shopware_elastic_search.client');

            return array_keys($client->indices()->getAliases());
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sw:es:switch:alias')
            ->setDescription('Allows to switch live-system aliases.')
            ->addArgument('shopId', InputArgument::REQUIRED)
            ->addArgument('type', InputArgument::REQUIRED, 'Mapping type of the elasticsearch index (e.g. product, property)')
            ->addArgument('index', InputArgument::REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shopId = $input->getArgument('shopId');
        $type = $input->getArgument('type');
        $indexName = $input->getArgument('index');

        /** @var Shop $shop */
        $shop = $this->container->get('shopware_storefront.shop_gateway_dbal')->get($shopId);

        /** @var ShopIndex $index */
        $index = $this->container->get('shopware_elastic_search.index_factory')
            ->createShopIndex($shop, $type);

        /** @var Client $client */
        $client = $this->container->get('shopware_elastic_search.client');

        $exist = $client->indices()->exists(['index' => $indexName]);
        if (!$exist) {
            throw new \RuntimeException(sprintf('Index "%s" does not exist', $indexName));
        }

        $actions = [
            ['add' => ['index' => $indexName, 'alias' => $index->getName()]],
        ];

        $current = $client->indices()->getAlias(['name' => $index->getName()]);
        $current = array_keys($current);
        foreach ($current as $value) {
            $actions[] = ['remove' => ['index' => $value, 'alias' => $index->getName()]];
        }
        $client->indices()->updateAliases(['body' => ['actions' => $actions]]);
    }
}
