<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
        /** @var Shopware\Models\Site\Repository $siteRepository */
        $siteRepository = $this->get('models')->getRepository('Shopware\Models\Site\Site');
        $sites = $siteRepository->getSitesByShopId(Shopware()->Shop()->getId());

        $staticPages = array(
            'name' => 'SitemapStaticPages',
            'link' => '',
            'sub' => array()
        );

        foreach ($sites as $site) {
            $site['hideOnSitemap'] = !$this->filterLink($site['link']);

            $staticPages['sub'][$site['id']] = array_merge(
                $site,
                $this->getSitemapArray(
                    $site['id'],
                    $site['description'],
                    'custom',
                    'sCustom',
                    $site['link']
                )
            );

            foreach ($site['children'] as $child) {
                $child['hideOnSitemap'] = !$this->filterLink($child['link']);

                $staticPages['sub'][$site['id']]['sub'][] = array_merge(
                    $child,
                    $this->getSitemapArray(
                        $child['id'],
                        $child['description'],
                        'custom',
                        'sCustom',
                        $child['link']
                    )
                );
            }
        }

        return $staticPages;
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

        if (empty($userParams['sViewport'])) {
            return false;
        }

        $blacklist = array('sitemap', 'sitemapXml');

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
        $builder = $this->get('models')->createQueryBuilder();
        $builder->select(array('supplier', 'attribute'))
            ->from('Shopware\Models\Article\Supplier', 'supplier')
            ->leftJoin('supplier.attribute', 'attribute');

        $suppliers = $builder->getQuery()->getArrayResult();

        $supplierPages = array(
            'name' => 'SitemapSupplierPages',
            'link' => '',
            'sub' => array()
        );

        foreach ($suppliers as $supplier) {
            $supplierPages['sub'][] = array_merge(
                $supplier,
                $this->getSitemapArray(
                    $supplier['id'],
                    $supplier['name'],
                    'supplier',
                    'sSupplier'
                )
            );
        }

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

        $builder = $emotionRepository->getCampaigns();
        $campaigns = $builder->getQuery()->getArrayResult();

        $landingPages = array(
            'name' => 'SitemapLandingPages',
            'link' => '',
            'sub' => array()
        );

        foreach ($campaigns as $campaign) {
            $landingPages['sub'][] = array_merge(
                $campaign[0],
                array('categoryId' => $campaign['categoryId']),
                $this->getSitemapArray(
                    $campaign[0]['id'],
                    $campaign[0]['name'],
                    'campaign',
                    'emotionId',
                    array('sCategory' => $campaign['categoryId'])
                )
            );
        }

        return $landingPages;
    }

    /**
     * Helper function to create a sitemap readable array
     * If $link is an array, it will be used as additional params for link assembling
     * @param integer $id
     * @param string $name
     * @param string $viewport
     * @param string $idParam
     * @param string|array $link
     * @return array
     */
    private function getSitemapArray($id, $name, $viewport, $idParam, $link = '')
    {
        if(is_array($link) || !strlen($link)){
            $userParams = array(
                'sViewport' => $viewport,
                $idParam => $id
            );

            if(is_array($link))
            {
                $userParams = array_merge($userParams, $link);
            }

            $link = $this->Front()->Router()->assemble($userParams);
        }

        return array(
            'id' => $id,
            'name' => $name,
            'link' => $link
        );
    }
}
