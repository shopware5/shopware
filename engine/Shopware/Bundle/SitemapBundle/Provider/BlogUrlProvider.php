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

use DateTime;
use Doctrine\DBAL\Connection;
use Shopware\Bundle\SitemapBundle\Struct\Url;
use Shopware\Bundle\SitemapBundle\UrlProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Routing;
use Shopware\Models\Category\Category;

class BlogUrlProvider implements UrlProviderInterface
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
        $blogs = [];

        $categoryRepository = $this->modelManager->getRepository(Category::class);
        $query = $categoryRepository->getBlogCategoriesByParentQuery($parentId);
        $blogCategories = $query->getArrayResult();

        $blogIds = [];

        foreach ($blogCategories as $blogCategory) {
            $blogIds[] = $blogCategory['id'];
        }

        if (count($blogIds) === 0) {
            return [];
        }

        $qb = $this->modelManager->getConnection()->createQueryBuilder();
        $statement = $qb
            ->addSelect('id, category_id, DATE(display_date) as changed')
            ->from('s_blog', 'blog')
            ->where('active = 1')
            ->andWhere('category_id IN (:ids)')
            ->setParameter('ids', $blogIds, Connection::PARAM_INT_ARRAY)
            ->execute();

        while ($blog = $statement->fetch()) {
            $blog['changed'] = new DateTime($blog['changed']);
            $blog['urlParams'] = [
                'sViewport' => 'blog',
                'sAction' => 'detail',
                'sCategory' => $blog['category_id'],
                'blogArticle' => $blog['id'],
            ];

            $blogs[] = $blog;
        }

        $routes = $this->router->generateList(array_column($blogs, 'urlParams'), $routingContext);
        $urls = [];

        for ($i = 0, $routeCount = count($routes); $i < $routeCount; ++$i) {
            $urls[] = new Url($routes[$i], $blogs[$i]['changed'], 'weekly');
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
}
