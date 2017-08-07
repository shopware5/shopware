<?php

namespace Shopware\Framework\Routing;

use Shopware\SeoUrl\Struct\SeoUrl;

interface UrlResolverInterface
{
    public function getPathInfo(int $shopId, string $url): ?SeoUrl;

    public function getUrl(int $shopId, string $pathInfo): ?SeoUrl;
}