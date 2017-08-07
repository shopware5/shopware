<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\Product;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\ReferenceField;

class ProductManufacturerUuidField extends ReferenceField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('productManufacturerUuid', 'product_manufacturer_uuid', 'product', $constraintBuilder);
    }
}