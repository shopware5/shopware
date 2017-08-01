<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductEsd;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class ProductDetailIdField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('productDetailId', 'product_detail_id', $constraintBuilder);
    }

}