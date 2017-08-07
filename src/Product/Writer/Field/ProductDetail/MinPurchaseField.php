<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductDetail;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class MinPurchaseField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('minPurchase', 'min_purchase', 'product_detail', $constraintBuilder);
    }
}