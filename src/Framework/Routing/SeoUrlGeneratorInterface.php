<?php

namespace Shopware\Framework\Routing;

use Shopware\Bundle\StoreFrontBundle\Context\TranslationContext;

interface SeoUrlGeneratorInterface
{
    /**
     * @param int $shopId
     * @param int $offset
     * @param int $limit
     * @return SeoRoute[]
     */
    public function fetch(int $shopId, TranslationContext $context, int $offset, int $limit): array;

    public function fetchCount(int $shopId): int;

    public function getName(): string;
}