<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Api;

class DateDefaultUpdateField extends DateField implements DefaultUpdateField
{
    public function getValue()
    {
        return $this->getValueTransformer()->transform(new \DateTimeImmutable());
    }
}