<?php
declare(strict_types=1);

namespace Shopware\Framework\Config;

interface ConfigServiceInterface
{
    public function getByShop(array $shop): array;
    public function hydrate(array $config): array;
}