<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Api;

interface Filter
{
    public function filter($value);
}