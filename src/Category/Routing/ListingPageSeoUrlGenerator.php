<?php

namespace Shopware\Category\Routing;

use Cocur\Slugify\SlugifyInterface;
use Doctrine\DBAL\Connection;
use Shopware\Category\Gateway\CategoryRepository;
use Shopware\Category\Struct\CategoryCollection;
use Shopware\Context\TranslationContext;
use Shopware\Framework\Routing\Router;
use Shopware\Framework\Routing\SeoRoute;
use Shopware\Framework\Routing\SeoUrlGeneratorInterface;
use Shopware\Search\Condition\ActiveCondition;
use Shopware\Search\Condition\ShopCondition;
use Shopware\Search\Criteria;
use Shopware\Search\SearchResult;

class ListingPageSeoUrlGenerator implements SeoUrlGeneratorInterface
{
    const ROUTE_NAME = 'listing_page';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var CategoryRepository
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

    public function __construct(
        Connection $connection,
        CategoryRepository $categoryRepository,
        SlugifyInterface $slugify,
        Router $generator
    ) {
        $this->connection = $connection;
        $this->categoryRepository = $categoryRepository;
        $this->slugify = $slugify;
        $this->generator = $generator;
    }

    public function fetch(int $shopId, TranslationContext $context, int $offset, int $limit): array
    {
        $criteria = new Criteria();
        $criteria->offset($offset);
        $criteria->limit($limit);
        $criteria->addCondition(new ShopCondition([$shopId]));
        $criteria->addCondition(new ActiveCondition(true));

        $result = $this->categoryRepository->search($criteria, $context);

        $categories = $this->categoryRepository->read(
            $this->extractIds($result),
            $context,
            CategoryRepository::FETCH_LIST
        );

        $routes = [];
        foreach ($result as $row) {
            $id = (int)$row['id'];

            $url = $this->generator->generate(self::ROUTE_NAME, ['id' => $id]);

            $seoUrl = $this->buildSeoUrl($id, $categories);

            if (!$seoUrl || !$url) {
                continue;
            }

            $routes[] = new SeoRoute(self::ROUTE_NAME, $url, $seoUrl);
        }

        return $routes;
    }

    public function getName(): string
    {
        return self::ROUTE_NAME;
    }

    private function extractIds(SearchResult $result): array
    {
        $ids = $result->fetchColumn('id');
        $paths = $result->fetchColumn('path');

        foreach ($paths as $path) {
            foreach ($path as $id) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    private function buildSeoUrl(int $id, CategoryCollection $categories): ?string
    {
        $category = $categories->get($id);
        if (!$category->getParentId() || $category->isShopCategory()) {
            return null;
        }

        $name = $this->slugify->slugify($category->getName());
        $parent = $this->buildSeoUrl($category->getParentId(), $categories);
        if (!$parent) {
            return $name . '/';
        }

        return $parent . $name . '/';
    }
}