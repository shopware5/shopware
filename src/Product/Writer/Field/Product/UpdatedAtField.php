<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\Product;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\DateDefaultUpdateField;

class UpdatedAtField extends DateDefaultUpdateField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('updatedAt', 'updated_at', 'product', $constraintBuilder);
    }
}