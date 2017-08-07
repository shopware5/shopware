<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductTopSellerRo;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\DateField;

class ClearedAtField extends DateField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('clearedAt', 'cleared_at', 'product_top_seller_ro', $constraintBuilder);
    }
}