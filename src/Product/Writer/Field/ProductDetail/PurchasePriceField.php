<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductDetail;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\FloatField;

class PurchasePriceField extends FloatField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('purchasePrice', 'purchase_price', 'product_detail', $constraintBuilder);
    }
}