<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductSimilarShownRo;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class ViewedField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('viewed', 'viewed', 'product_similar_shown_ro', $constraintBuilder);
    }
}