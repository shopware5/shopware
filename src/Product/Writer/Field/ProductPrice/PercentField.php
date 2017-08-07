<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductPrice;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\FloatField;

class PercentField extends FloatField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('percent', 'percent', 'product_price', $constraintBuilder);
    }
}