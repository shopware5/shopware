<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductPrice;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\ReferenceField;

class ProductDetailUuidField extends ReferenceField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('productDetailUuid', 'product_detail_uuid', 'product_price', $constraintBuilder);
    }
}