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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RebuildSeoIndexCommand extends ShopwareCommand
{
    /** @var \Shopware_Components_SeoIndex */
    protected $seoIndex;

    /** @var \sRewriteTable */
    protected $rewriteTable;

    /** @var \sCategories */
    protected $categories;

    /** @var \Doctrine\DBAL\Connection */
    protected $database;

    /** @var \Shopware_Components_Modules */
    protected $modules;

    /** @var \Shopware\Components\Model\ModelManager */
    protected $modelManager;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:rebuild:seo:index')
            ->setDescription('Rebuild the SEO index')
            ->addArgument('shopId', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'The Id of the shop')
            ->setHelp('The <info>%command.name%</info> rebuilds the SEO index')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->database = $this->container->get('dbal_connection');
        $this->modules = $this->container->get('modules');
        $this->modelManager = $this->container->get('models');
        $this->seoIndex = $this->container->get('SeoIndex');
        $this->rewriteTable = $this->modules->RewriteTable();

        $shops = $input->getArgument('shopId');

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

        $this->seoIndex->registerShop($shops[0]);
        $this->rewriteTable->sCreateRewriteTableCleanup();

        foreach ($shops as $shopId) {
            $output->writeln('Rebuilding SEO index for shop ' . $shopId);
            /** @var $repository \Shopware\Models\Shop\Repository */
            $repository = $this->modelManager->getRepository('Shopware\Models\Shop\Shop');
            $shop = $repository->getActiveById($shopId);

            if ($shop === null) {
                throw new \Exception('No valid shop id passed');
            }

            $shop->registerResources();
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
            $this->rewriteTable->sCreateRewriteTableBlog();
            $this->rewriteTable->createManufacturerUrls($context);
            $this->rewriteTable->sCreateRewriteTableStatic();
        }

        $output->writeln('The SEO index was rebuild successfully.');
    }
}
