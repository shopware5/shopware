<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\Product;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class PriceGroupIdField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('priceGroupId', 'price_group_id', 'product', $constraintBuilder);
    }
}