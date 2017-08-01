<?php

namespace Shopware\Framework\Routing;

interface SeoUrlReaderInterface
{
    public function fetchUrl(int $shopId, string $seoUrl): ?string;

    public function fetchSeoUrl(int $shopId, string $url): ?string;

    public function fetchAll(int $shopId): array;
}