<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductPriceAttribute;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\ReferenceField;

class ProductPriceUuidField extends ReferenceField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('productPriceUuid', 'product_price_uuid', 'product_price_attribute', $constraintBuilder);
    }
}