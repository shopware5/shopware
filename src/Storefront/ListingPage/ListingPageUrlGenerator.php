<?php

namespace Shopware\Storefront\ListingPage;

use Cocur\Slugify\SlugifyInterface;
use Doctrine\DBAL\Connection;
use Shopware\Category\Gateway\CategoryRepository;
use Shopware\Category\Struct\CategoryCollection;
use Shopware\Category\Struct\CategoryIdentity;
use Shopware\Context\TranslationContext;
use Shopware\Framework\Routing\Router;
use Shopware\Search\Condition\CanonicalCondition;
use Shopware\Search\Condition\ForeignKeyCondition;
use Shopware\Search\Condition\NameCondition;
use Shopware\SeoUrl\Gateway\SeoUrlRepository;
use Shopware\SeoUrl\Generator\SeoUrlGeneratorInterface;
use Shopware\SeoUrl\Struct\SeoUrl;
use Shopware\Search\Condition\ActiveCondition;
use Shopware\Search\Condition\ShopCondition;
use Shopware\Search\Criteria;

class ListingPageUrlGenerator implements SeoUrlGeneratorInterface
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

    /**
     * @var SeoUrlRepository
     */
    private $seoUrlRepository;

    public function __construct(
        Connection $connection,
        CategoryRepository $categoryRepository,
        SlugifyInterface $slugify,
        Router $generator,
        SeoUrlRepository $seoUrlRepository
    ) {
        $this->connection = $connection;
        $this->categoryRepository = $categoryRepository;
        $this->slugify = $slugify;
        $this->generator = $generator;
        $this->seoUrlRepository = $seoUrlRepository;
    }

    public function fetch(int $shopId, TranslationContext $context, int $offset, int $limit): array
    {
        $criteria = new Criteria();
        $criteria->offset($offset);
        $criteria->limit($limit);
        $criteria->addCondition(new ShopCondition([$shopId]));
        $criteria->addCondition(new ActiveCondition(true));

        $result = $this->categoryRepository->search($criteria, $context);
        $categories = $this->categoryRepository->read($result->getIdsIncludingPaths(), $context, CategoryRepository::FETCH_LIST);

        $criteria = new Criteria();
        $criteria->addCondition(new CanonicalCondition(true));
        $criteria->addCondition(new ForeignKeyCondition($categories->getIds()));
        $criteria->addCondition(new NameCondition([self::ROUTE_NAME]));
        $criteria->addCondition(new ShopCondition([$shopId]));
        $existingCanonicals = $this->seoUrlRepository->search($criteria, $context);

        $routes = [];
        /** @var CategoryIdentity $identity */
        foreach ($result as $identity) {
            $pathInfo = $this->generator->generate(self::ROUTE_NAME, ['id' => $identity->getId()]);

            $url = $this->buildSeoUrl($identity->getId(), $categories);

            if (!$url || !$pathInfo) {
                continue;
            }

            $url = $url . '/' . $identity->getId();

            $routes[] = new SeoUrl(
                null,
                $shopId,
                self::ROUTE_NAME,
                $identity->getId(),
                $pathInfo,
                $url,
                new \DateTime(),
                !$existingCanonicals->hasPathInfo($pathInfo)
            );
        }

        return $routes;
    }

    public function getName(): string
    {
        return self::ROUTE_NAME;
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