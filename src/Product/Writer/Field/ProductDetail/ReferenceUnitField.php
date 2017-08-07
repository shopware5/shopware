<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductDetail;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\FloatField;

class ReferenceUnitField extends FloatField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('referenceUnit', 'reference_unit', 'product_detail', $constraintBuilder);
    }
}