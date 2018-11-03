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

namespace Shopware\Bundle\SitemapBundle\Provider;

use Shopware\Bundle\SitemapBundle\Struct\Url;
use Shopware\Bundle\SitemapBundle\UrlProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Routing;
use Shopware\Models\Category\Category;

class CategoryUrlProvider implements UrlProviderInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Routing\Router
     */
    private $router;

    /**
     * @var bool
     */
    private $allExported = false;

    /**
     * @param ModelManager   $modelManager
     * @param Routing\Router $router
     */
    public function __construct(ModelManager $modelManager, Routing\Router $router)
    {
        $this->modelManager = $modelManager;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls(Routing\Context $routingContext, ShopContextInterface $shopContext)
    {
        if ($this->allExported) {
            return [];
        }

        $parentId = $shopContext->getShop()->getCategory()->getId();
        $categoryRepository = $this->modelManager->getRepository(Category::class);
        $categories = $categoryRepository->getActiveChildrenList($parentId, $shopContext->getFallbackCustomerGroup()->getId());

        foreach ($categories as $key => &$category) {
            if (!empty($category['external'])) {
                unset($categories[$key]);
            }

            $category['urlParams'] = [
                'sViewport' => 'cat',
                'sCategory' => $category['id'],
                'title' => $category['name'],
            ];

            if ($category['blog']) {
                $category['urlParams']['sViewport'] = 'blog';
            }
        }

        unset($category);

        $categories = array_values($categories);

        $routes = $this->router->generateList(array_column($categories, 'urlParams'), $routingContext);
        $urls = [];

        for ($i = 0, $routeCount = count($routes); $i < $routeCount; ++$i) {
            $urls[] = new Url($routes[$i], $categories[$i]['changed'], 'weekly');
        }

        $this->allExported = true;

        return $urls;
    }

    /**
     * Resets the provider for next sitemap generation
     */
    public function reset()
    {
        $this->allExported = false;
    }
}
