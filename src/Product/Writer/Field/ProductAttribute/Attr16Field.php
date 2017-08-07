<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductAttribute;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\StringField;

class Attr16Field extends StringField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('attr16', 'attr16', 'product_attribute', $constraintBuilder);
    }
}