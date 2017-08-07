<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductPrice;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\StringField;

class ToField extends StringField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('to', 'to', 'product_price', $constraintBuilder);
    }
}