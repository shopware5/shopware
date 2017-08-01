<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductAttribute;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class ProductDetailsIdField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('productDetailsId', 'product_details_id', $constraintBuilder);
    }

}