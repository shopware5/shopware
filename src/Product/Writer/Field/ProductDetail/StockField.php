<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductDetail;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class StockField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('stock', 'stock', 'product_detail', $constraintBuilder);
    }
}