<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Api;

class ValueTransformerDate implements ValueTransformer
{
    public function transform($phpValue)
    {
        if(!$phpValue instanceof \DateTimeInterface) {
            throw new \InvalidArgumentException('Unable to do this');
        }

        return $phpValue->format('Y-m-d H:i:s');
    }
}