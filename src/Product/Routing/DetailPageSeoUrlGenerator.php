<?php

namespace Shopware\Product\Routing;

use Cocur\Slugify\SlugifyInterface;
use Doctrine\DBAL\Connection;
use Shopware\Context\TranslationContext;
use Shopware\Framework\Routing\Router;
use Shopware\Framework\Routing\SeoRoute;
use Shopware\Framework\Routing\SeoUrlGeneratorInterface;
use Shopware\Product\Gateway\ProductRepository;
use Shopware\Search\Condition\ActiveCondition;
use Shopware\Search\Condition\ShopCondition;
use Shopware\Search\Criteria;

class DetailPageSeoUrlGenerator implements SeoUrlGeneratorInterface
{
    const ROUTE_NAME = 'detail_page';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ProductRepository
     */
    private $repository;

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
        ProductRepository $repository,
        SlugifyInterface $slugify,
        Router $generator
    ) {
        $this->connection = $connection;
        $this->repository = $repository;
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

        $result = $this->repository->search($criteria, $context);

        $products = $this->repository->read(
            $result->fetchColumn('number'),
            $context,
            ProductRepository::FETCH_MINIMAL
        );

        $routes = [];
        foreach ($result as $row) {
            $number = $row['number'];

            $url = $this->generator->generate(self::ROUTE_NAME, ['number' => $number]);

            $product = $products->get($number);

            $seoUrl = $this->slugify->slugify($product->getName());

            if (!$seoUrl || !$url) {
                continue;
            }

            $routes[] = new SeoRoute(self::ROUTE_NAME, $url, $seoUrl);
        }

        return $routes;
    }

    public function fetchCount(int $shopId): int
    {
        return 1;
    }

    public function getName(): string
    {
        return self::ROUTE_NAME;
    }
}