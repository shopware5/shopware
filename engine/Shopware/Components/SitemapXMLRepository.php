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

namespace Shopware\Components;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\QueryBuilder;
use Shopware\Bundle\SearchBundle\Condition\LastProductIdCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductNumberSearchInterface;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Category\Category;

/**
 * @deprecated Will be removed in Shopware 5.6
 */
class SitemapXMLRepository
{
    /**
     * @var ModelManager
     */
    private $em;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var ProductNumberSearchInterface
     */
    private $productNumberSearch;

    /**
     * @var StoreFrontCriteriaFactoryInterface
     */
    private $storeFrontCriteriaFactory;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param int $batchSize
     */
    public function __construct(
        ProductNumberSearchInterface $productNumberSearch,
        StoreFrontCriteriaFactoryInterface $storeFrontCriteriaFactory,
        ModelManager $em,
        ContextServiceInterface $contextService,
        $batchSize = 10000
    ) {
        $this->em = $em;
        $this->connection = $this->em->getConnection();
        $this->contextService = $contextService;
        $this->productNumberSearch = $productNumberSearch;
        $this->storeFrontCriteriaFactory = $storeFrontCriteriaFactory;
        $this->batchSize = $batchSize;
    }

    /**
     * @return array
     */
    public function getSitemapContent()
    {
        $parentId = $this->contextService->getShopContext()->getShop()->getCategory()->getId();
        $categories = $this->readCategoryUrls($parentId);
        $categoryIds = array_column($categories, 'id');

        return [
            'categories' => $categories,
            'articles' => $this->readProductUrls($categoryIds),
            'blogs' => $this->readBlogUrls($parentId),
            'customPages' => $this->readStaticUrls(),
            'suppliers' => $this->readSupplierUrls(),
            'landingPages' => $this->readLandingPageUrls(),
        ];
    }

    /**
     * Print category urls
     *
     * @param int $parentId
     *
     * @return array
     */
    private function readCategoryUrls($parentId)
    {
        /** @var array<array<string, mixed>> $categories */
        $categories = $this->em
            ->getRepository(Category::class)
            ->getActiveChildrenList($parentId, $this->contextService->getShopContext()->getFallbackCustomerGroup()->getId());

        foreach ($categories as &$category) {
            $category['show'] = empty($category['external']);

            $category['urlParams'] = [
                'sViewport' => 'cat',
                'sCategory' => $category['id'],
                'title' => $category['name'],
            ];

            if ($category['blog']) {
                $category['urlParams']['sViewport'] = 'blog';
            }
        }

        return $categories;
    }

    /**
     * Read product urls
     *
     * @param int[] $categoryIds
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array
     */
    private function readProductUrls(array $categoryIds)
    {
        if (empty($categoryIds)) {
            return [];
        }

        $criteria = $this->storeFrontCriteriaFactory->createBaseCriteria($categoryIds, $this->contextService->getShopContext());

        $productIds = $this->readProductUrlsRecursive($criteria);

        $statement = $this->connection->executeQuery(
            'SELECT id,changetime FROM s_articles WHERE id IN (:articleIds)',
            [':articleIds' => $productIds],
            [':articleIds' => Connection::PARAM_INT_ARRAY]
        );

        $products = [];
        while ($product = $statement->fetch()) {
            $product['changed'] = new \DateTime($product['changetime']);
            $product['urlParams'] = [
                'sViewport' => 'detail',
                'sArticle' => $product['id'],
            ];

            $products[] = $product;
        }

        return $products;
    }

    /**
     * Reads all product urls recursive
     *
     * @return array
     */
    private function readProductUrlsRecursive(Criteria $criteria)
    {
        $result = [];
        $criteria->limit($this->batchSize);

        $productNumberSearchResult = $this->productNumberSearch->search(
            $criteria,
            $this->contextService->getShopContext()
        );

        $products = $productNumberSearchResult->getProducts();

        if (empty($products)) {
            return $result;
        }

        foreach ($products as $product) {
            $result[] = $product->getId();
        }

        sort($result, SORT_NUMERIC);

        $lastProductId = $result[count($result) - 1];

        $criteria->removeBaseCondition('last_product_id');
        $criteria->addBaseCondition(new LastProductIdCondition($lastProductId));

        return array_merge($result, $this->readProductUrlsRecursive($criteria));
    }

    /**
     * Reads the blog item urls
     *
     * @param int $parentId
     *
     * @return array
     */
    private function readBlogUrls($parentId)
    {
        $blogs = [];

        $categoryRepository = $this->em->getRepository(\Shopware\Models\Category\Category::class);
        $query = $categoryRepository->getBlogCategoriesByParentQuery($parentId);
        $blogCategories = $query->getArrayResult();

        $blogIds = [];
        foreach ($blogCategories as $blogCategory) {
            $blogIds[] = $blogCategory['id'];
        }
        if (empty($blogIds)) {
            return $blogs;
        }

        $sql = '
            SELECT id, category_id, DATE(display_date) as changed
            FROM s_blog
            WHERE active = 1 AND category_id IN(?)
        ';

        $result = $this->connection->executeQuery(
            $sql,
            [$blogIds],
            [Connection::PARAM_INT_ARRAY]
        );

        while ($blog = $result->fetch()) {
            $blog['changed'] = new \DateTime($blog['changed']);
            $blog['urlParams'] = [
                'sViewport' => 'blog',
                'sAction' => 'detail',
                'sCategory' => $blog['category_id'],
                'blogArticle' => $blog['id'],
            ];

            $blogs[] = $blog;
        }

        return $blogs;
    }

    /**
     * Helper function to Read the static pages urls
     *
     * @return array
     */
    private function readStaticUrls()
    {
        $shopId = $this->contextService->getShopContext()->getShop()->getId();
        $sites = $this->getSitesByShopId($shopId);

        foreach ($sites as $site) {
            if (!empty($site['children'])) {
                $sites = array_merge($sites, $site['children']);
            }
        }

        foreach ($sites as &$site) {
            $site['urlParams'] = [
                'sViewport' => 'custom',
                'sCustom' => $site['id'],
            ];

            $site['show'] = $this->filterLink($site['link'], $site['urlParams']);
        }

        return $sites;
    }

    /**
     * Helper function to read all static pages of a shop from the database
     *
     * @param int $shopId
     *
     * @return array
     */
    private function getSitesByShopId($shopId)
    {
        $sql = '
            SELECT static_groups.key
            FROM s_core_shop_pages shopPages
              INNER JOIN s_cms_static_groups static_groups
                ON static_groups.id = shopPages.group_id
            WHERE shopPages.shop_id = ?
        ';

        $statement = $this->connection->executeQuery($sql, [$shopId]);

        $keys = $statement->fetchAll(\PDO::FETCH_COLUMN);

        $siteRepository = $this->em->getRepository('Shopware\Models\Site\Site');

        $sites = [];
        foreach ($keys as $key) {
            $current = $siteRepository->getSitesByNodeNameQueryBuilder($key, $shopId)
                ->resetDQLPart('from')
                ->from(\Shopware\Models\Site\Site::class, 'sites', 'sites.id')
                ->andWhere('sites.active = 1')
                ->getQuery()
                ->getArrayResult();

            $sites += $current;
        }

        return $sites;
    }

    /**
     * Helper function to filter predefined links, which should not be in the sitemap (external links, sitemap links itself)
     * Returns false, if the link is not allowed
     *
     * @param string $link
     * @param array  $userParams
     *
     * @return bool
     */
    private function filterLink($link, &$userParams)
    {
        if (empty($link)) {
            return true;
        }

        $userParams = parse_url($link, PHP_URL_QUERY);
        parse_str($userParams, $userParams);

        $blacklist = ['', 'sitemap', 'sitemapXml'];

        if (in_array($userParams['sViewport'], $blacklist)) {
            return false;
        }

        return true;
    }

    /**
     * Helper function to read the supplier pages urls
     *
     * @return array
     */
    private function readSupplierUrls()
    {
        $suppliers = $this->getSupplierForSitemap();
        foreach ($suppliers as &$supplier) {
            $supplier['urlParams'] = [
                'sViewport' => 'listing',
                'sAction' => 'manufacturer',
                'sSupplier' => $supplier['id'],
            ];
        }

        return $suppliers;
    }

    /**
     * Gets all suppliers that have products for the current shop
     *
     * @throws \Exception
     *
     * @return array
     */
    private function getSupplierForSitemap()
    {
        $context = $this->contextService->getShopContext();
        $categoryId = $context->getShop()->getCategory()->getId();

        /** @var QueryBuilder $query */
        $query = $this->connection->createQueryBuilder();
        $query->select(['manufacturer.id', 'manufacturer.name']);

        $query->from('s_articles_supplier', 'manufacturer');
        $query->innerJoin('manufacturer', 's_articles', 'product', 'product.supplierID = manufacturer.id')
            ->innerJoin('product', 's_articles_categories_ro', 'categories', 'categories.articleID = product.id AND categories.categoryID = :categoryId')
            ->setParameter(':categoryId', $categoryId);

        $query->groupBy('manufacturer.id');

        /** @var \PDOStatement $statement */
        $statement = $query->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Helper function to read the landing pages urls
     *
     * @return array
     */
    private function readLandingPageUrls()
    {
        $emotionRepository = $this->em->getRepository('Shopware\Models\Emotion\Emotion');

        $shopId = $this->contextService->getShopContext()->getShop()->getId();

        $builder = $emotionRepository->getCampaignsByShopId($shopId);
        $campaigns = $builder->getQuery()->getArrayResult();

        foreach ($campaigns as &$campaign) {
            $campaign['show'] = $this->filterCampaign($campaign['validFrom'], $campaign['validTo']);
            $campaign['urlParams'] = [
                'sViewport' => 'campaign',
                'emotionId' => $campaign['id'],
            ];
        }

        return $campaigns;
    }

    /**
     * Helper function to filter emotion campaigns
     * Returns false, if the campaign starts later or is outdated
     *
     * @param \DateTimeInterface|null $from
     * @param \DateTimeInterface|null $to
     *
     * @return bool
     */
    private function filterCampaign($from = null, $to = null)
    {
        $now = new \DateTime();

        if (isset($from) && $now < $from) {
            return false;
        }

        if (isset($to) && $now > $to) {
            return false;
        }

        return true;
    }
}
