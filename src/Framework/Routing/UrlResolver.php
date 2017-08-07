<?php

namespace Shopware\Framework\Routing;

use Shopware\Context\TranslationContext;
use Shopware\Search\Condition\CanonicalCondition;
use Shopware\Search\Condition\PathInfoCondition;
use Shopware\Search\Condition\ShopCondition;
use Shopware\Search\Condition\UrlCondition;
use Shopware\Search\Criteria;
use Shopware\SeoUrl\Gateway\SeoUrlRepository;
use Shopware\SeoUrl\Struct\SeoUrl;

class UrlResolver implements UrlResolverInterface
{
    /**
     * @var SeoUrlRepository
     */
    private $seoUrlRepository;

    public function __construct(SeoUrlRepository $seoUrlRepository)
    {
        $this->seoUrlRepository = $seoUrlRepository;
    }

    public function getPathInfo(int $shopId, string $url): ?SeoUrl
    {
        $criteria = new Criteria();
        $criteria->addCondition(new ShopCondition([$shopId]));
        $criteria->addCondition(new UrlCondition([$url]));

        $context = new TranslationContext($shopId, true, null);

        $urls = $this->seoUrlRepository->search($criteria, $context);

        return $urls->getByUrl($url);
    }

    public function getUrl(int $shopId, string $pathInfo): ?SeoUrl
    {
        $criteria = new Criteria();
        $criteria->addCondition(new ShopCondition([$shopId]));
        $criteria->addCondition(new PathInfoCondition([$pathInfo]));
        $criteria->addCondition(new CanonicalCondition(true));

        $context = new TranslationContext($shopId, true, null);

        $urls = $this->seoUrlRepository->search($criteria, $context);

        return $urls->getByPathInfo($pathInfo);
    }
}