<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductInformationAttribute;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\ReferenceField;

class ProductInformationUuidField extends ReferenceField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('productInformationUuid', 'product_information_uuid', 'product_information_attribute', $constraintBuilder);
    }
}