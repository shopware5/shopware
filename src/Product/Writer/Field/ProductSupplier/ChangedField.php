<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductSupplier;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\DateField;

class ChangedField extends DateField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('changed', 'changed', 'product_supplier', $constraintBuilder);
    }
}