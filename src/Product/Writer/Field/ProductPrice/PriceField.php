<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductPrice;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\FloatField;

class PriceField extends FloatField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('price', 'price', 'product_price', $constraintBuilder);
    }
}