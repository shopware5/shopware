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
use Shopware\Bundle\ESIndexingBundle\Console\EvaluationHelperInterface;
use Shopware\Bundle\ESIndexingBundle\IdentifierSelector;
use Shopware\Bundle\ESIndexingBundle\ShopIndexerInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\ShopGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Commands\ShopwareCommand;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop as ShopModel;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IndexPopulateCommand extends ShopwareCommand implements CompletionAwareInterface
{
    private ModelManager $modelManager;

    private ShopIndexerInterface $shopIndexer;

    private EvaluationHelperInterface $evaluationHelper;

    private IdentifierSelector $identifierSelector;

    private ShopGatewayInterface $shopGateway;

    public function __construct(
        ModelManager $modelManager,
        ShopIndexerInterface $shopIndexer,
        EvaluationHelperInterface $evaluationHelper,
        IdentifierSelector $identifierSelector,
        ShopGatewayInterface $shopGateway
    ) {
        parent::__construct();
        $this->modelManager = $modelManager;
        $this->shopIndexer = $shopIndexer;
        $this->evaluationHelper = $evaluationHelper;
        $this->identifierSelector = $identifierSelector;
        $this->shopGateway = $shopGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        if ($optionName === 'shopId') {
            $queryBuilder = $this->modelManager->getRepository(ShopModel::class)->createQueryBuilder('shop');

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
            ->addOption('shopId', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The shop to populate (multiple Ids -> shopId={1,2})')
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
        $helper = new ConsoleProgressHelper($output);

        $this->evaluationHelper->setOutput($output);
        $this->evaluationHelper->setActive(!$input->getOption('no-evaluation'));
        $this->evaluationHelper->setStopOnError($input->getOption('stop-on-error'));

        foreach ($this->getShops($input, $output) as $shop) {
            $output->writeln("\n## Indexing shop " . $shop->getName() . ' ##');
            $this->shopIndexer->index($shop, $helper, $input->getOption('index'));
        }

        return 0;
    }

    /**
     * @return array<Shop>
     */
    private function getShops(InputInterface $input, OutputInterface $output): array
    {
        $shopIds = $input->getOption('shopId');
        if ($shopIds === []) {
            return $this->identifierSelector->getShops();
        }

        $shops = $this->shopGateway->getList($shopIds);
        $existingShopIds = array_keys($shops);

        $shopIdsNotFound = array_diff_key($shopIds, $existingShopIds);
        if ($shopIdsNotFound !== []) {
            $output->writeln(sprintf('<error>Shops with following IDs not found: %s</error>', implode(', ', $shopIdsNotFound)));
        }

        return $shops;
    }
}
