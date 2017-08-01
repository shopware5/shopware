<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductPriceAttribute;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class PriceIdField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('priceId', 'price_id', $constraintBuilder);
    }

}