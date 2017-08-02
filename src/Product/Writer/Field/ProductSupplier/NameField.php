<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductSupplier;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\StringField;

class NameField extends StringField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('name', 'name', 'product_supplier', $constraintBuilder);
    }
}