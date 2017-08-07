<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductDetail;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class PurchaseStepsField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('purchaseSteps', 'purchase_steps', 'product_detail', $constraintBuilder);
    }
}