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

use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class AnalyzeCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:es:analyze')
            ->setDescription('Helper tool to test own analyzers.')
            ->addArgument('shopId', InputOption::VALUE_REQUIRED, null, 1)
            ->addArgument('analyzer', InputOption::VALUE_REQUIRED)
            ->addArgument('query', InputOption::VALUE_REQUIRED)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shopId = $input->getArgument('shopId');
        $query = $input->getArgument('query');
        $analyzer = $input->getArgument('analyzer');

        $shop = $this->container->get('shopware_storefront.shop_gateway_dbal')->getList([$shopId]);
        $shop = array_shift($shop);

        $client = $this->container->get('shopware_elastic_search.client');
        $index = $this->container->get('shopware_elastic_search.index_factory')->createShopIndex($shop);

        $analyzed = $client->indices()->analyze([
            'index' => $index->getName(),
            'analyzer' => $analyzer,
            'text' => $query,
        ]);

        $tokens = $analyzed['tokens'];

        $table = new Table($output);
        $table->setHeaders(['Token', 'Start', 'End', 'Type', 'position'])
            ->setRows($tokens)
            ->render();
    }
}
