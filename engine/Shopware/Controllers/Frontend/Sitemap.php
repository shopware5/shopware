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

/**
 * @category  Shopware
 * @package   Shopware\Controllers\Frontend
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Frontend_Sitemap extends Enlight_Controller_Action
{
    /**
     * Shows a category tree
     */
    public function indexAction()
    {
        $categoryTree = Shopware()->Modules()->sCategories()->sGetWholeCategoryTree();
        $additionalTrees = $this->getAdditionalTrees();

        $additionalTrees = Shopware()->Events()->filter(
            'Shopware_Modules_Sitemap_indexAction',
            $additionalTrees,
            array('subject' => $this)
        );

        $categoryTree = array_merge($categoryTree, $additionalTrees);
        $this->View()->sCategoryTree = $categoryTree;
    }

    /**
     * Helper function to get additional page trees
     * @return array
     */
    private function getAdditionalTrees()
    {
        return array(
            $this->getCustomPages(),
            $this->getSupplierPages(),
            $this->getLandingPages()
        );
    }

    /**
     * Helper function to get all custom pages of the shop
     * @return array
     */
    private function getCustomPages()
    {
        $sites = $this->getSitesByShopId(Shopware()->Shop()->getId());

        foreach ($sites as &$site) {
            $site = $this->convertSite($site);
        }

        $staticPages = array(
            'name' => 'SitemapStaticPages',
            'link' => '',
            'sub' => $sites
        );

        return $staticPages;
    }

    /**
     * Helper function to read all static pages of a shop from the database
     * @return array
     */
    private function getSitesByShopId($shopId)
    {
        $sql = "
            SELECT groups.key
            FROM s_core_shop_pages shopPages
              INNER JOIN s_cms_static_groups groups
                ON groups.id = shopPages.group_id
            WHERE shopPages.shop_id = ?
        ";

        $statement = Shopware()->Db()->executeQuery($sql, array($shopId));

        $keys = $statement->fetchAll(PDO::FETCH_COLUMN);

        /** @var Shopware\Models\Site\Repository $siteRepository */
        $siteRepository = $this->get('models')->getRepository('Shopware\Models\Site\Site');

        $sites = array();
        foreach ($keys as $key) {
            $current = $siteRepository->getSitesByNodeNameQueryBuilder($key, $shopId)
                ->resetDQLPart('from')
                ->from('Shopware\Models\Site\Site', 'sites', 'sites.id')
                ->getQuery()
                ->getArrayResult();

            $sites += $current;
        }

        return $sites;
    }

    /**
     * Recursive helper function to convert a site to correct sitemap format
     * @param $site
     * @return mixed
     */
    private function convertSite($site)
    {
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
                $child = $this->convertSite($child);
            }
            $site['sub'] = $site['children'];
        }

        return $site;
    }

    /**
     * Helper function to filter predefined links, which should not be in the sitemap (external links, sitemap links itself)
     * Returns false, if the link is not allowed
     * @param string $link
     * @return bool
     */
    private function filterLink($link)
    {
        if (empty($link)) {
            return true;
        }

        $userParams = parse_url($link, PHP_URL_QUERY);
        parse_str($userParams, $userParams);

        $blacklist = array('', 'sitemap', 'sitemapXml');

        if (in_array($userParams['sViewport'], $blacklist)) {
            return false;
        }

        return true;
    }

    /**
     * Helper function to get all supplier pages
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
                    array('sAction' => 'manufacturer')
                )
            );
        }

        $supplierPages = array(
            'name' => 'SitemapSupplierPages',
            'link' => '',
            'sub' => $suppliers
        );

        return $supplierPages;
    }

    /**
     * Helper function to get all landing pages
     * @return array
     */
    private function getLandingPages()
    {
        /** @var Shopware\Models\Emotion\Repository $emotionRepository */
        $emotionRepository = $this->get('models')->getRepository('Shopware\Models\Emotion\Emotion');

        $shopId = Shopware()->Shop()->getId();
        $fallbackId = null;

        $fallbackShop = Shopware()->Shop()->getFallback();

        if (!empty($fallbackShop)) {
            $fallbackId = $fallbackShop->getId();
        }

        $translator = new Shopware_Components_Translation();

        $builder = $emotionRepository->getCampaignsByShopId($shopId);
        $campaigns = $builder->getQuery()->getArrayResult();

        foreach ($campaigns as &$campaign) {
            $translation = $translator->readWithFallback($shopId, $fallbackId, 'emotion', $campaign['id']);

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

        $landingPages = array(
            'name' => 'SitemapLandingPages',
            'link' => '',
            'sub' => $campaigns
        );

        return $landingPages;
    }

    /**
     * Helper function to filter emotion campaigns
     * Returns false, if the campaign starts later or is outdated
     * @param null $from
     * @param null $to
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
     * @param integer $id
     * @param string $name
     * @param string $viewport
     * @param string $idParam
     * @param string|array|null $link
     * @return array
     */
    private function getSitemapArray($id, $name, $viewport, $idParam, $link = null)
    {
        $userParams = array();

        if (is_string($link)) {
            $userParams = parse_url($link, PHP_URL_QUERY);
            parse_str($userParams, $userParams);
        }

        if (empty($userParams)) {
            $userParams = array(
                'sViewport' => $viewport,
                $idParam => $id
            );
        }

        if (is_array($link)) {
            $userParams = array_merge($userParams, $link);
        }

        $link = $this->Front()->Router()->assemble($userParams);

        return array(
            'id' => $id,
            'name' => $name,
            'link' => $link
        );
    }

    /**
     * Gets all suppliers that have products for the current shop
     *
     * @return array
     * @throws Exception
     */
    private function getSupplierForSitemap()
    {
        $context = $this->get('shopware_storefront.context_service')->getShopContext();
        $categoryId = $context->getShop()->getCategory()->getId();

        /**@var $query QueryBuilder */
        $query = $this->get('dbal_connection')->createQueryBuilder();
        $query->select(['manufacturer.id', 'manufacturer.name']);

        $query->from('s_articles_supplier', 'manufacturer');
        $query->innerJoin('manufacturer', 's_articles', 'product', 'product.supplierID = manufacturer.id')
            ->innerJoin('product', 's_articles_categories_ro', 'categories', 'categories.articleID = product.id AND categories.categoryID = :categoryId')
            ->setParameter(':categoryId', $categoryId);

        $query->groupBy('manufacturer.id');

        /**@var $statement PDOStatement */
        $statement = $query->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
