<?php

namespace Shopware\Framework\Routing\SeoUrlGenerator;

use Cocur\Slugify\SlugifyInterface;
use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Category\Category;
use Shopware\Bundle\StoreFrontBundle\Context\TranslationContext;
use Shopware\Framework\Routing\Router;
use Shopware\Framework\Routing\SeoRoute;
use Shopware\Framework\Routing\SeoUrlGeneratorInterface;

class ListingSeoUrlGenerator implements SeoUrlGeneratorInterface
{
    const ROUTE_NAME = 'listing';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var
     */
    private $categoryRepository;

    /**
     * @var SlugifyInterface
     */
    private $slugify;

    /**
     * @var Router
     */
    private $generator;

    public function fetch(int $shopId, TranslationContext $context, int $offset, int $limit): array
    {
        $criteria = new Criteria();
        $criteria->offset($offset);
        $criteria->limit($limit);
        $criteria->addCondition(new ShopCondition([$shopId]));
        $criteria->addCondition(new ActiveCondition());
        $criteria->addSorting(new IdSorting('ASC'));

        $ids = $this->categoryRepository->search($criteria);

        $categories = $this->categoryRepository->read($ids, $context, CategoryRepository::FETCH_LIST);

        $routes = [];

        /** @var Category $category */
        foreach ($categories as $category) {

            $url = $this->generator->generate(self::ROUTE_NAME, ['id' => $category->getId()]);

            $seoUrl = $this->slugify->slugify($category->getBreadcrumb());

            $routes[] = new SeoRoute(self::ROUTE_NAME, $url, $seoUrl);
        }

        return $routes;
    }

    public function fetchCount(int $shopId): int
    {
        return (int) $this->connection->fetchColumn("SELECT COUNT(id) FROM s_categories WHERE active = 1");
    }

    public function getName(): string
    {
        return self::ROUTE_NAME;
    }
}