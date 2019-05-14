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

use Shopware\Bundle\SitemapBundle\Repository\CategoryRepositoryInterface;
use Shopware\Bundle\SitemapBundle\Struct\Url;
use Shopware\Bundle\SitemapBundle\UrlProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Routing\Context as RoutingContext;
use Shopware\Components\Routing\RouterInterface;
use Shopware\Models\Category\Category;

class CategoryUrlProvider implements UrlProviderInterface
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $repository;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var bool
     */
    private $allExported = false;

    public function __construct(CategoryRepositoryInterface $repository, RouterInterface $router)
    {
        $this->repository = $repository;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->allExported = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls(RoutingContext $routingContext, ShopContextInterface $shopContext)
    {
        if ($this->allExported) {
            return [];
        }

        $categories = $this->repository->getCategories($shopContext);

        /** @var array<string, array> $category */
        foreach ($categories as $key => &$category) {
            if (!empty($category['external'])) {
                unset($categories[$key]);
                continue;
            }

            $category['urlParams'] = [
                'sViewport' => 'cat',
                'sCategory' => $category['id'],
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
            $urls[] = new Url($routes[$i], $categories[$i]['changed'], 'weekly', Category::class, $categories[$i]['id']);
        }

        $this->allExported = true;

        return $urls;
    }
}
