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

namespace ShopwarePlugins\HttpCache;

use Enlight_Controller_Action as Controller;
use Enlight_Controller_Request_Request as Request;
use Enlight_View_Default as View;
use Shopware\Bundle\EmotionBundle\ComponentHandler\ArticleComponentHandler;
use Shopware\Bundle\EmotionBundle\ComponentHandler\ArticleSliderComponentHandler;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class CacheIdCollector
{
    /**
     * Returns an array of affected cache ids for this $controller
     *
     * @return array
     */
    public function getCacheIdsFromController(Controller $controller, ShopContextInterface $context)
    {
        $request = $controller->Request();
        $view = $controller->View();
        $controllerName = $this->getControllerRoute($request);

        switch ($controllerName) {
            case 'frontend/blog':
                return $this->getBlogCacheIds($request, $view);

            case 'widgets/listing':
                return $this->getAjaxListingCacheIds($request, $view);

            case 'frontend/index':
                return $this->getHomePageCacheIds($context);

            case 'widgets/recommendation':
                return $this->getRecommendationCacheIds($view);

            case 'frontend/detail':
                return $this->getDetailCacheIds($request);

            case 'widgets/emotion':
                return $this->getEmotionCacheIds($view);

            case 'frontend/listing':
                return $this->getListingCacheIds($request, $view);

            case 'frontend/custom':
                return $this->getStaticSiteCacheIds($request);

            default:
                return [];
        }
    }

    private function getControllerRoute(Request $request)
    {
        return implode('/', [
            strtolower($request->getModuleName()),
            strtolower($request->getControllerName()),
        ]);
    }

    /**
     * @return array
     */
    private function getBlogCacheIds(Request $request, View $view)
    {
        $cacheIds = [];

        $cacheIds[] = 'c' . (int) $request->getParam('sCategory');

        $blogPost = $view->getAssign('sArticle');
        foreach ($blogPost['assignedArticles'] as $article) {
            $cacheIds[] = 'a' . $article['id'];
        }

        return $cacheIds;
    }

    private function getAjaxListingCacheIds(Request $request, View $view)
    {
        $cacheIds = [];

        $categoryId = (int) $request->getParam('sCategory');
        $cacheIds[] = 'c' . $categoryId;

        foreach ($view->getAssign('sArticles') as $article) {
            $cacheIds[] = 'a' . $article['articleID'];
        }

        foreach ($view->getAssign('sCharts') as $article) {
            $cacheIds[] = 'a' . $article['articleID'];
        }

        return $cacheIds;
    }

    /**
     * @return array
     */
    private function getHomePageCacheIds(ShopContextInterface $context)
    {
        $categoryId = (int) $context->getShop()->getCategory()->getId();

        return ['c' . $categoryId];
    }

    /**
     * @return array
     */
    private function getRecommendationCacheIds(View $view)
    {
        $cacheIds = [];
        $article = $view->getAssign('sArticle');

        foreach ($article['sRelatedArticles'] as $article) {
            $cacheIds[] = 'a' . $article['articleID'];
        }
        foreach ($article['sSimilarArticles'] as $article) {
            $cacheIds[] = 'a' . $article['articleID'];
        }

        return $cacheIds;
    }

    /**
     * @return array
     */
    private function getDetailCacheIds(Request $request)
    {
        return ['a' . $request->getParam('sArticle', 0)];
    }

    /**
     * @return array
     */
    private function getEmotionCacheIds(View $view)
    {
        $cacheIds = [];

        /** @var \Shopware\Bundle\EmotionBundle\Struct\Emotion $emotion */
        foreach ($view->getAssign('sEmotions') as $emotion) {
            $cacheIds[] = 'e' . $emotion['id'];

            foreach ($emotion['elements'] as $element) {
                if ($element['component']['type'] === ArticleComponentHandler::COMPONENT_NAME) {
                    /** @var \Shopware\Bundle\StoreFrontBundle\Struct\ListProduct $product */
                    $product = $element['data']['product'];
                    if (!$product) {
                        continue;
                    }
                    $cacheIds[] = 'a' . $product->getId();
                } elseif ($element['component']['type'] === ArticleSliderComponentHandler::COMPONENT_NAME) {
                    /** @var \Shopware\Bundle\StoreFrontBundle\Struct\ListProduct[] $products */
                    $products = $element['data']['products'];
                    foreach ($products as $product) {
                        $cacheIds[] = 'a' . $product->getId();
                    }
                }
            }
        }

        return $cacheIds;
    }

    /**
     * @return array
     */
    private function getListingCacheIds(Request $request, View $view)
    {
        $cacheIds = [];

        $categoryId = (int) $request->getParam('sCategory');
        $cacheIds[] = 'c' . $categoryId;

        foreach ($view->getAssign('sArticles') as $article) {
            $cacheIds[] = 'a' . $article['articleID'];
        }

        return $cacheIds;
    }

    /**
     * @return array
     */
    private function getStaticSiteCacheIds(Request $request)
    {
        $staticSiteId = $request->getParam('sCustom');

        return ['s' . (int) $staticSiteId];
    }
}
