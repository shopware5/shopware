<?php

namespace Shopware\Framework\Component\Config;

interface ConfigServiceInterface
{
    public function getByShop(array $shop): array;
    public function hydrate(array $config): array;
}