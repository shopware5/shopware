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

use DateTime;
use Doctrine\DBAL\Connection;
use Shopware\Bundle\SitemapBundle\Struct\Url;
use Shopware\Bundle\SitemapBundle\UrlProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\RouterInterface;
use Shopware\Models\Blog\Blog;
use Shopware\Models\Category\Category;
use Shopware_Components_Translation as Translation;

class BlogUrlProvider implements UrlProviderInterface
{
    private ModelManager $modelManager;

    private RouterInterface $router;

    private Translation $translation;

    private bool $allExported = false;

    public function __construct(ModelManager $modelManager, RouterInterface $router, Translation $translation)
    {
        $this->modelManager = $modelManager;
        $this->router = $router;
        $this->translation = $translation;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls(Context $routingContext, ShopContextInterface $shopContext)
    {
        if ($this->allExported) {
            return [];
        }

        $shopId = $shopContext->getShop()->getId();
        $parentId = $shopContext->getShop()->getCategory()->getId();

        $query = $this->modelManager->getRepository(Category::class)->getBlogCategoriesByParentQuery($parentId);
        $blogCategories = $query->getArrayResult();

        $blogIds = [];

        foreach ($blogCategories as $blogCategory) {
            $blogIds[] = (int) $blogCategory['id'];
        }

        if (\count($blogIds) === 0) {
            return [];
        }

        $blogs = $this->modelManager->getConnection()->createQueryBuilder()
            ->addSelect('blog.id, blog.category_id, DATE(blog.display_date) as changed')
            ->from('s_blog', 'blog')
            ->where('blog.active = 1')
            ->innerJoin('blog', 's_categories', 'cat', 'cat.id = blog.category_id')
            ->andWhere('category_id IN (:ids)')
            ->andWhere('cat.shops IS NULL OR cat.shops LIKE :shopLike')
            ->andWhere('blog.shop_ids IS NULL OR blog.shop_ids LIKE :shopLike')
            ->setParameter(':shopLike', '%|' . $shopId . '|%')
            ->setParameter('ids', $blogIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAllAssociative();

        $blogIds = array_column($blogs, 'id');
        $blogTranslations = $this->fetchTranslations($blogIds, $shopContext);

        foreach ($blogs as $key => &$blog) {
            if (isset($blogTranslations[$blog['id']]) && empty($blogTranslations[$blog['id']]['active'])) {
                unset($blogs[$key]);
                continue;
            }

            $blog['changed'] = new DateTime($blog['changed']);
            $blog['urlParams'] = [
                'sViewport' => 'blog',
                'sAction' => 'detail',
                'sCategory' => $blog['category_id'],
                'blogArticle' => $blog['id'],
            ];
        }

        unset($blog);
        $blogs = array_values($blogs);

        $routes = $this->router->generateList(array_column($blogs, 'urlParams'), $routingContext);
        $urls = [];

        for ($i = 0, $routeCount = \count($routes); $i < $routeCount; ++$i) {
            $urls[] = new Url($routes[$i], $blogs[$i]['changed'], 'weekly', Blog::class, $blogs[$i]['id']);
        }

        $this->allExported = true;

        return $urls;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->allExported = false;
    }

    /**
     * @param array<int> $ids
     *
     * @return array<array<string, string>>
     */
    private function fetchTranslations(array $ids, ShopContextInterface $shopContext): array
    {
        $data = $this->translation->readBatchWithFallback($shopContext->getShop()->getId(), $shopContext->getShop()->getFallbackId(), 'blog', $ids, false);
        $translation = [];

        foreach ($data as $row) {
            $translation[$row['objectkey']] = $row['objectdata'];
        }

        return $translation;
    }
}
