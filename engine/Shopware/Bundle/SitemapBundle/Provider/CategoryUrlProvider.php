<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\SitemapBundle\Provider;

use Doctrine\ORM\AbstractQuery;
use Shopware\Bundle\SitemapBundle\Struct\Url;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Routing\Context;
use Shopware\Models\Category\Category;

class CategoryUrlProvider extends BaseUrlProvider
{
    /**
     * {@inheritdoc}
     */
    public function getUrls(Context $routingContext, ShopContextInterface $shopContext)
    {
        if ($this->allExported) {
            return [];
        }

        $parentId = $shopContext->getShop()->getCategory()->getId();
        $categoryRepository = $this->modelManager->getRepository(Category::class);
        $categories = $categoryRepository->getActiveChildrenList($parentId, $shopContext->getFallbackCustomerGroup()->getId(), null, $shopContext->getShop()->getId());

        foreach ($categories as $key => &$category) {
            if (!empty($category['external'])) {
                unset($categories[$key]);
                continue;
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

        $parentCategory = $categoryRepository->getDetailQuery($parentId)->getSingleResult(AbstractQuery::HYDRATE_ARRAY);

        // Add home page
        array_unshift($categories, [
            'id' => $parentCategory['id'],
            'changed' => $parentCategory['changed'],
            'urlParams' => [
                'sViewport' => 'index',
            ],
        ]);

        unset($category);

        $categories = array_values($categories);

        $routes = $this->router->generateList(array_column($categories, 'urlParams'), $routingContext);
        $urls = [];

        for ($i = 0, $routeCount = \count($routes); $i < $routeCount; ++$i) {
            $urls[] = new Url($routes[$i], $categories[$i]['changed'], 'weekly', Category::class, $categories[$i]['id']);
        }

        $this->allExported = true;

        return $urls;
    }
}
