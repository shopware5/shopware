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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Emotion\Emotion;
use Shopware\Models\Shop\DetachedShop;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Site\Site;

class Shopware_Controllers_Frontend_Sitemap extends Enlight_Controller_Action
{
    /**
     * Shows a category tree
     */
    public function indexAction()
    {
        $categoryTree = $this->getCategoryTree();
        $additionalTrees = $this->getAdditionalTrees();

        $additionalTrees = $this->container->get('events')->filter(
            'Shopware_Modules_Sitemap_indexAction',
            $additionalTrees,
            ['subject' => $this]
        );

        $categoryTree = array_merge($categoryTree, $additionalTrees);
        $this->View()->assign('sCategoryTree', $categoryTree);
    }

    private function getCategoryTree(): array
    {
        $shop = $this->container->get('shop');
        if (!$shop instanceof Shop) {
            throw new RuntimeException('Shop is not initialized correctly in DI container');
        }
        $categoryTree = $this->container->get('modules')->Categories()->sGetWholeCategoryTree(null, null, $shop->getId());

        $categoryTranslations = $this->fetchTranslations('category', $this->getTranslationKeys(
            $categoryTree,
            'id',
            'sub'
        ));

        return $this->translateCategoryTree($categoryTree, $categoryTranslations);
    }

    private function translateCategoryTree(array $categoryTree, array $translations): array
    {
        foreach ($categoryTree as $key => $category) {
            $translation = $this->fetchTranslation($category['id'], $translations);

            if (!empty($translation['description'])) {
                $translation['name'] = $translation['description'];
            }

            if (!empty($translation['external'])) {
                $translation['link'] = $translation['external'];
            }

            $categoryTree[$key] = array_merge($category, $translation);

            if (!empty($category['sub'])) {
                $categoryTree[$key]['sub'] = $this->translateCategoryTree($category['sub'], $translations);
            }
        }

        return $categoryTree;
    }

    /**
     * Helper function to get additional page trees
     */
    private function getAdditionalTrees(): array
    {
        return [
            $this->getCustomPages(),
            $this->getSupplierPages(),
            $this->getLandingPages(),
        ];
    }

    /**
     * Helper function to get all custom pages of the shop
     */
    private function getCustomPages(): array
    {
        /** @var DetachedShop $shop */
        $shop = $this->container->get('shop');

        $sites = $this->getSitesByShopId($shop->getId());

        $translations = $this->fetchTranslations('page', $this->getTranslationKeys(
            $sites,
            'id',
            'children'
        ));

        foreach ($sites as &$site) {
            $site = $this->convertSite($site, $translations);
        }

        return [
            'name' => 'SitemapStaticPages',
            'link' => '',
            'sub' => $sites,
        ];
    }

    /**
     * @param string $keyField
     * @param string $recursiveField
     *
     * @return int[]
     */
    private function getTranslationKeys(array $array, $keyField, $recursiveField)
    {
        $translationkeys = [];

        foreach ($array as $data) {
            $translationkeys[] = $data[$keyField];

            if (!empty($data[$recursiveField])) {
                $translationkeys = array_merge($translationkeys, $this->getTranslationKeys($data[$recursiveField], $keyField, $recursiveField));
            }
        }

        return $translationkeys;
    }

    /**
     * Helper function to read all static pages of a shop from the database
     */
    private function getSitesByShopId(int $shopId): array
    {
        $sql = '
            SELECT shopGroups.key
            FROM s_core_shop_pages shopPages
              INNER JOIN s_cms_static_groups shopGroups
                ON shopGroups.id = shopPages.group_id
            WHERE shopPages.shop_id = ?
        ';

        $statement = $this->container->get('db')->executeQuery($sql, [$shopId]);
        $keys = $statement->fetchAll(PDO::FETCH_COLUMN);

        $siteRepository = $this->get(ModelManager::class)->getRepository(Site::class);

        $sites = [];
        foreach ($keys as $key) {
            $current = $siteRepository->getSitesByNodeNameQueryBuilder($key, $shopId)
                ->resetDQLPart('from')
                ->from(Site::class, 'sites', 'sites.id')
                ->andWhere('sites.active = true')
                ->getQuery()
                ->getArrayResult();

            $sites += $current;
        }

        return $sites;
    }

    /**
     * Recursive helper function to convert a site to correct sitemap format
     *
     * @param array $site
     *
     * @return array
     */
    private function convertSite($site, array $translations)
    {
        $site = array_merge($site, $this->fetchTranslation($site['id'], $translations));
        $site['hideOnSitemap'] = !$this->filterLink($site['link']);

        $site = array_merge(
            $site,
            $this->getSitemapArray(
                $site['id'],
                $site['description'],
                'custom',
                'sCustom',
                $site['link']
            )
        );

        if (isset($site['children'])) {
            foreach ($site['children'] as &$child) {
                $child = $this->convertSite($child, $translations);
            }
            $site['sub'] = $site['children'];
        }

        return $site;
    }

    /**
     * @param int[] $ids
     */
    private function fetchTranslations(string $type, array $ids): array
    {
        /** @var DetachedShop $shop */
        $shop = $this->container->get('shop');

        $shopId = $shop->getId();
        $fallbackShop = $shop->getFallback();

        $fallbackId = null;
        if ($fallbackShop !== null) {
            $fallbackId = $fallbackShop->getId();
        }

        $translator = $this->container->get(Shopware_Components_Translation::class);

        return $translator->readBatchWithFallback($shopId, $fallbackId, $type, $ids, false);
    }

    private function fetchTranslation(int $objectKey, array $translations): array
    {
        foreach ($translations as $translation) {
            if ((int) $translation['objectkey'] === $objectKey) {
                return $translation['objectdata'];
            }
        }

        return [];
    }

    /**
     * Helper function to filter predefined links, which should not be in the sitemap (external links, sitemap links itself)
     * Returns false, if the link is not allowed
     */
    private function filterLink(string $link): bool
    {
        if (empty($link)) {
            return true;
        }

        $userParams = parse_url($link, PHP_URL_QUERY);
        parse_str($userParams, $userParams);

        $blacklist = ['', 'sitemap', 'sitemapXml'];

        if (\in_array($userParams['sViewport'], $blacklist, true)) {
            return false;
        }

        return true;
    }

    /**
     * Helper function to get all supplier pages
     */
    private function getSupplierPages(): array
    {
        $suppliers = $this->getSupplierForSitemap();

        foreach ($suppliers as &$supplier) {
            $supplier = array_merge(
                $supplier,
                $this->getSitemapArray(
                    $supplier['id'],
                    $supplier['name'],
                    'listing',
                    'sSupplier',
                    ['sAction' => 'manufacturer']
                )
            );
        }

        return [
            'name' => 'SitemapSupplierPages',
            'link' => '',
            'sub' => $suppliers,
        ];
    }

    /**
     * Helper function to get all landing pages
     */
    private function getLandingPages(): array
    {
        $emotionRepository = $this->get(ModelManager::class)->getRepository(Emotion::class);

        $shop = $this->container->get('shop');
        if (!$shop instanceof Shop) {
            throw new RuntimeException('Shop is not initialized correctly in DI container');
        }

        $campaigns = $emotionRepository->getCampaignsByShopId($shop->getId())->getQuery()->getArrayResult();
        $translations = $this->fetchTranslations('emotion', array_column($campaigns, 'id'));

        foreach ($campaigns as &$campaign) {
            $translation = $this->fetchTranslation($campaign['id'], $translations);

            $translation['seo_title'] = $translation['seoTitle'];
            $translation['seo_keywords'] = $translation['seoKeywords'];
            $translation['seo_description'] = $translation['seoDescription'];

            $campaign = array_merge($campaign, $translation);

            $campaign['hideOnSitemap'] = !$this->filterCampaign(
                $campaign['validFrom'],
                $campaign['validTo']
            );

            $campaign = array_merge(
                $campaign,
                $this->getSitemapArray(
                    $campaign['id'],
                    $campaign['name'],
                    'campaign',
                    'emotionId'
                )
            );
        }

        return [
            'name' => 'SitemapLandingPages',
            'link' => '',
            'sub' => $campaigns,
        ];
    }

    /**
     * Helper function to filter emotion campaigns
     * Returns false, if the campaign starts later or is outdated
     */
    private function filterCampaign(DateTimeInterface $from = null, DateTimeInterface $to = null): bool
    {
        $now = new DateTime();

        if (isset($from) && $now < $from) {
            return false;
        }

        if (isset($to) && $now > $to) {
            return false;
        }

        return true;
    }

    /**
     * Helper function to create a sitemap readable array
     * If $link is an array, it will be used as additional params for link assembling
     *
     * @param string|array|null $link
     */
    private function getSitemapArray(int $id, string $name, string $viewport, string $idParam, $link = null): array
    {
        $userParams = [];

        if (\is_string($link)) {
            $userParams = parse_url($link, PHP_URL_QUERY);
            parse_str($userParams, $userParams);
        }

        if (empty($userParams)) {
            $userParams = [
                'sViewport' => $viewport,
                $idParam => $id,
            ];
        }

        if (\is_array($link)) {
            $userParams = array_merge($userParams, $link);
        }

        $link = $this->Front()->Router()->assemble($userParams);

        return [
            'id' => $id,
            'name' => $name,
            'link' => $link,
        ];
    }

    /**
     * Gets all suppliers that have products for the current shop
     *
     * @throws Exception
     */
    private function getSupplierForSitemap(): array
    {
        $context = $this->get(ContextServiceInterface::class)->getShopContext();
        $categoryId = $context->getShop()->getCategory()->getId();

        /** @var QueryBuilder $query */
        $query = $this->get(Connection::class)->createQueryBuilder();
        $query->select(['manufacturer.id', 'manufacturer.name']);

        $query->from('s_articles_supplier', 'manufacturer');
        $query->innerJoin('manufacturer', 's_articles', 'product', 'product.supplierID = manufacturer.id')
            ->innerJoin('product', 's_articles_categories_ro', 'categories', 'categories.articleID = product.id AND categories.categoryID = :categoryId')
            ->setParameter(':categoryId', $categoryId);

        $query->groupBy('manufacturer.id');

        /** @var PDOStatement $statement */
        $statement = $query->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
