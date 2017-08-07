<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductAttribute;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\TextField;

class Attr10Field extends TextField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('attr10', 'attr10', 'product_attribute', $constraintBuilder);
    }
}