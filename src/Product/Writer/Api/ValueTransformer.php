<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Api;

interface ValueTransformer
{
    /**
     * @param $phpValue
     * @return int|float|string
     */
    public function transform($phpValue);

}