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
use Shopware\Bundle\StoreFrontBundle\Struct\TranslationContext;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class SwitchAliasCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sw:es:switch:alias')
            ->setDescription('Allows to switch live-system aliases.')
            ->addArgument('shopId', InputArgument::REQUIRED)
            ->addArgument('index', InputArgument::REQUIRED)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shopId = $input->getArgument('shopId');
        $indexName = $input->getArgument('index');

        /** @var $shop Shop */
        $context = new TranslationContext($shopId, true, null);
        $shop = $this->container->get('shopware_storefront.shop_gateway_dbal')->getList([$shopId], $context);
        $shop = array_shift($shop);

        /** @var $index ShopIndex */
        $index = $this->container->get('shopware_elastic_search.index_factory')
            ->createShopIndex($shop);

        /** @var $client Client */
        $client = $this->container->get('shopware_elastic_search.client');

        $exist = $client->indices()->exists(['index' => $indexName]);
        if (!$exist) {
            throw new \RuntimeException(sprintf('Index %s not exist', $indexName));
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
