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

use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Shop\DetachedShop;
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

    /**
     * @return array
     */
    private function getCategoryTree()
    {
        $shop = $this->container->get('shop');
        $categoryTree = $this->container->get('modules')->sCategories()->sGetWholeCategoryTree(null, null, $shop->getId());

        $categoryTranslations = $this->fetchTranslations('category', $this->getTranslationKeys(
            $categoryTree,
            'id',
            'sub'
        ));

        return $this->translateCategoryTree($categoryTree, $categoryTranslations);
    }

    /**
     * @return array
     */
    private function translateCategoryTree(array $categoryTree, array $translations)
    {
        foreach ($categoryTree as $key => $category) {
            $translation = $this->fetchTranslation($category['id'], $translations);

            if (!empty($translation['description'])) {
                $translation['name'] = $translation['description'];
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
     *
     * @return array
     */
    private function getAdditionalTrees()
    {
        return [
            $this->getCustomPages(),
            $this->getSupplierPages(),
            $this->getLandingPages(),
        ];
    }

    /**
     * Helper function to get all custom pages of the shop
     *
     * @return array
     */
    private function getCustomPages()
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

        $staticPages = [
            'name' => 'SitemapStaticPages',
            'link' => '',
            'sub' => $sites,
        ];

        return $staticPages;
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
                $translationkeys += $this->getTranslationKeys($data[$recursiveField], $keyField, $recursiveField);
            }
        }

        return $translationkeys;
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
            SELECT shopGroups.key
            FROM s_core_shop_pages shopPages
              INNER JOIN s_cms_static_groups shopGroups
                ON shopGroups.id = shopPages.group_id
            WHERE shopPages.shop_id = ?
        ';

        $statement = $this->container->get('db')->executeQuery($sql, [$shopId]);
        $keys = $statement->fetchAll(PDO::FETCH_COLUMN);

        /** @var Shopware\Models\Site\Repository $siteRepository */
        $siteRepository = $this->get('models')->getRepository('Shopware\Models\Site\Site');

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
     * @param string $type
     * @param int[]  $ids
     *
     * @return array
     */
    private function fetchTranslations($type, array $ids)
    {
        /** @var DetachedShop $shop */
        $shop = $this->container->get('shop');

        $shopId = $shop->getId();
        $fallbackShop = $shop->getFallback();

        $fallbackId = null;
        if ($fallbackShop !== null) {
            $fallbackId = $fallbackShop->getId();
        }

        $translator = $this->container->get('translation');

        return $translator->readBatchWithFallback($shopId, $fallbackId, $type, $ids, false);
    }

    /**
     * @param int $objectKey
     *
     * @return array
     */
    private function fetchTranslation($objectKey, array $translations)
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
     *
     * @param string $link
     *
     * @return bool
     */
    private function filterLink($link)
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
     * Helper function to get all supplier pages
     *
     * @return array
     */
    private function getSupplierPages()
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

        $supplierPages = [
            'name' => 'SitemapSupplierPages',
            'link' => '',
            'sub' => $suppliers,
        ];

        return $supplierPages;
    }

    /**
     * Helper function to get all landing pages
     *
     * @return array
     */
    private function getLandingPages()
    {
        /** @var Shopware\Models\Emotion\Repository $emotionRepository */
        $emotionRepository = $this->get('models')->getRepository('Shopware\Models\Emotion\Emotion');

        /** @var DetachedShop $shop */
        $shop = $this->container->get('shop');

        $builder = $emotionRepository->getCampaignsByShopId($shop->getId());
        $campaigns = $builder->getQuery()->getArrayResult();
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

        $landingPages = [
            'name' => 'SitemapLandingPages',
            'link' => '',
            'sub' => $campaigns,
        ];

        return $landingPages;
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
     * @param int               $id
     * @param string            $name
     * @param string            $viewport
     * @param string            $idParam
     * @param string|array|null $link
     *
     * @return array
     */
    private function getSitemapArray($id, $name, $viewport, $idParam, $link = null)
    {
        $userParams = [];

        if (is_string($link)) {
            $userParams = parse_url($link, PHP_URL_QUERY);
            parse_str($userParams, $userParams);
        }

        if (empty($userParams)) {
            $userParams = [
                'sViewport' => $viewport,
                $idParam => $id,
            ];
        }

        if (is_array($link)) {
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
     *
     * @return array
     */
    private function getSupplierForSitemap()
    {
        $context = $this->get('shopware_storefront.context_service')->getShopContext();
        $categoryId = $context->getShop()->getCategory()->getId();

        /** @var QueryBuilder $query */
        $query = $this->get('dbal_connection')->createQueryBuilder();
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
