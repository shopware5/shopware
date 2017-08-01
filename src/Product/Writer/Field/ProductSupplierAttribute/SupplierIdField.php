<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductSupplierAttribute;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class SupplierIdField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('supplierId', 'supplier_id', $constraintBuilder);
    }

}