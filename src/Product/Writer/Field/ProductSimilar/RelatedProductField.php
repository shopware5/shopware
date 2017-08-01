<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductSimilar;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\StringField;

class RelatedProductField extends StringField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('relatedProduct', 'related_product', $constraintBuilder);
    }

}