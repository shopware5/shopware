<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Api;

class ValueTransformerNoOp implements ValueTransformer
{
    public function transform($phpValue)
    {
        return $phpValue;
    }
}