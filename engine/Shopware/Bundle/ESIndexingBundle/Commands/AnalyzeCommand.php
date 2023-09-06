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

namespace Shopware\Bundle\ESIndexingBundle\Commands;

use Elasticsearch\Client;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Shopware\Bundle\ESIndexingBundle\IndexFactory;
use Shopware\Bundle\StoreFrontBundle\Exception\StructNotFoundException;
use Shopware\Bundle\StoreFrontBundle\Gateway\ShopGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop as ShopStruct;
use Shopware\Commands\ShopwareCommand;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop as ShopModel;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyzeCommand extends ShopwareCommand implements CompletionAwareInterface
{
    public const DEFAULT_ANALYZERS = [
        'standard',
        'simple',
        'whitespace',
        'stop',
        'keyword',
        'pattern',
        'fingerprint',

        'arabic',
        'armenian',
        'basque',
        'bengali',
        'brazilian',
        'bulgarian',
        'catalan',
        'cjk',
        'czech',
        'danish',
        'dutch',
        'english',
        'finnish',
        'french',
        'galician',
        'german',
        'greek',
        'hindi',
        'hungarian',
        'indonesian',
        'irish',
        'italian',
        'latvian',
        'lithuanian',
        'norwegian',
        'persian',
        'portuguese',
        'romanian',
        'russian',
        'sorani',
        'spanish',
        'swedish',
        'turkish',
        'thai',
    ];

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
            $shopRepository = $this->getContainer()->get(ModelManager::class)->getRepository(ShopModel::class);
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

        if ($argumentName === 'analyzer') {
            $client = $this->container->get(Client::class);

            $recursive = new RecursiveIteratorIterator(
                new RecursiveArrayIterator($client->indices()->getMapping()),
                RecursiveIteratorIterator::SELF_FIRST
            );

            $analyzer = [];
            foreach ($recursive as $key => $value) {
                if ($key === 'analyzer') {
                    $analyzer[] = $value;
                }
            }

            return array_unique(array_merge($analyzer, self::DEFAULT_ANALYZERS));
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:es:analyze')
            ->setDescription('Helper tool to test own analyzers.')
            ->addArgument('shopId', InputOption::VALUE_REQUIRED, '', '1')
            ->addArgument('type', InputOption::VALUE_REQUIRED, 'Mapping type of the elasticsearch index (e.g. product, property)')
            ->addArgument('analyzer', InputOption::VALUE_REQUIRED)
            ->addArgument('query', InputOption::VALUE_REQUIRED)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shopId = (int) $input->getArgument('shopId');
        $type = $input->getArgument('type');
        $query = $input->getArgument('query');
        $analyzer = $input->getArgument('analyzer');

        $shop = $this->container->get(ShopGatewayInterface::class)->get($shopId);
        if (!$shop instanceof ShopStruct) {
            throw new StructNotFoundException(ShopStruct::class, $shopId);
        }
        $client = $this->container->get(Client::class);
        $index = $this->container->get(IndexFactory::class)->createShopIndex($shop, $type);

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

        return 0;
    }
}
