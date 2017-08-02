<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductAlsoBoughtRo;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class RelatedProductIdField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('relatedProductId', 'related_product_id', 'product_also_bought_ro', $constraintBuilder);
    }
}