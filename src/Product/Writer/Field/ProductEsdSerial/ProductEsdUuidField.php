<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductEsdSerial;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\ReferenceField;

class ProductEsdUuidField extends ReferenceField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('productEsdUuid', 'product_esd_uuid', 'product_esd_serial', $constraintBuilder);
    }
}