<?php

namespace Shopware\Framework\Routing;

use Shopware\Context\TranslationContext;

interface SeoUrlGeneratorInterface
{
    /**
     * @param int $shopId
     * @param TranslationContext $context
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function fetch(int $shopId, TranslationContext $context, int $offset, int $limit): array;

    public function getName(): string;
}