<?php
declare(strict_types=1);

namespace Shopware\Framework\Plugin;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class Plugin extends Bundle
{
    /**
     * @var bool
     */
    private $active;

    public function __construct(bool $active)
    {
        $this->active = $active;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param string $environment (dev, prod, test)
     * @return BundleInterface[]
     */
    public function registerBundles(string $environment): array
    {
        return [];
    }
}