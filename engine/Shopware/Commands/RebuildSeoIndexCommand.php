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

namespace Shopware\Commands;

use Shopware\Components\ContainerAwareEventManager;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Repository;
use Shopware\Models\Shop\Shop;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RebuildSeoIndexCommand extends ShopwareCommand implements CompletionAwareInterface
{
    /**
     * @var \Shopware_Components_SeoIndex
     */
    protected $seoIndex;

    /**
     * @var \sRewriteTable
     */
    protected $rewriteTable;

    /**
     * @var \sCategories
     */
    protected $categories;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $database;

    /**
     * @var \Shopware_Components_Modules
     */
    protected $modules;

    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    protected $modelManager;

    /**
     * @var ContainerAwareEventManager
     */
    protected $events;

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
            /** @var ModelManager $em */
            $em = $this->getContainer()->get('models');
            /** @var Repository $shopRepository */
            $shopRepository = $em->getRepository(Shop::class);
            $queryBuilder = $shopRepository->createQueryBuilder('shop');

            if (is_numeric($context->getCurrentWord())) {
                $queryBuilder->andWhere($queryBuilder->expr()->like('shop.id', ':id'))
                    ->setParameter('id', addcslashes($context->getCurrentWord(), '%_') . '%');
            }

            $result = $queryBuilder->select(['shop.id'])
                ->addOrderBy($queryBuilder->expr()->asc('shop.id'))
                ->getQuery()
                ->getArrayResult();

            $alreadyTakenShopIds = array_filter($context->getWords(), 'is_numeric');

            return array_diff(array_column($result, 'id'), $alreadyTakenShopIds);
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:rebuild:seo:index')
            ->setDescription('Rebuild the SEO index')
            /* @deprecated since 5.6, to be removed in 6.0 */
            ->addArgument('shopId', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'The Id of the shop (deprecated)')
            ->addOption('shopId', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The Id of the shop (multiple Ids -> shopId={1,2})')
            ->setHelp('The <info>%command.name%</info> rebuilds the SEO index')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->database = $this->container->get('dbal_connection');
        $this->modules = $this->container->get('modules');
        $this->modelManager = $this->container->get('models');
        $this->seoIndex = $this->container->get('seoindex');
        $this->rewriteTable = $this->modules->RewriteTable();
        $this->events = $this->container->get('events');

        $shops = null;

        if ($input->getArgument('shopId')) {
            $io = new SymfonyStyle($input, $output);
            $io->warning('Argument "shopId" will be replaced by option "--shopId" in the next major version');
            $shops = $input->getArgument('shopId');
        } elseif ($input->getOption('shopId')) {
            $shops = $input->getOption('shopId');
        }

        if (empty($shops)) {
            /** @var \Doctrine\DBAL\Query\QueryBuilder $query */
            $query = $this->database->createQueryBuilder();
            $shops = $query->select('id')
                ->from('s_core_shops', 'shops')
                ->where('active', 1)
                ->execute()
                ->fetchAll(\PDO::FETCH_COLUMN);
        }

        $currentTime = new \DateTime();

        $this->rewriteTable->sCreateRewriteTableCleanup();

        foreach ($shops as $shopId) {
            $output->writeln('Rebuilding SEO index for shop ' . $shopId);

            /** @var \Shopware\Models\Shop\Repository $repository */
            $repository = $this->modelManager->getRepository(\Shopware\Models\Shop\Shop::class);
            $shop = $repository->getActiveById($shopId);

            if ($shop === null) {
                throw new \RuntimeException('No valid shop id passed');
            }

            $this->container->get('shopware.components.shop_registration_service')->registerShop($shop);

            $this->modules->Categories()->baseId = $shop->getCategory()->getId();

            list($cachedTime, $elementId, $shopId) = $this->seoIndex->getCachedTime();

            $this->seoIndex->setCachedTime($currentTime->format('Y-m-d h:m:i'), $elementId, $shopId);
            $this->rewriteTable->baseSetup();

            $limit = 10000;
            $lastId = null;
            $lastUpdateVal = '0000-00-00 00:00:00';

            do {
                $lastUpdateVal = $this->rewriteTable->sCreateRewriteTableArticles($lastUpdateVal, $limit);
                $lastId = $this->rewriteTable->getRewriteArticleslastId();
            } while ($lastId !== null);

            $this->seoIndex->setCachedTime($currentTime->format('Y-m-d h:m:i'), $elementId, $shopId);

            $context = $this->container->get('shopware_storefront.context_service')->createShopContext($shopId);

            $this->rewriteTable->sCreateRewriteTableCategories();
            $this->rewriteTable->sCreateRewriteTableCampaigns();
            $this->rewriteTable->sCreateRewriteTableContent();
            $this->rewriteTable->sCreateRewriteTableBlog(null, null, $context);
            $this->rewriteTable->createManufacturerUrls($context);
            $this->rewriteTable->sCreateRewriteTableStatic();
            $this->rewriteTable->createContentTypeUrls($context);

            $this->events->notify(
                'Shopware_Command_RebuildSeoIndexCommand_CreateRewriteTable',
                [
                    'shopContext' => $context,
                    'cachedTime' => $currentTime,
                ]
            );
        }

        $output->writeln('The SEO index was rebuild successfully.');
    }
}
