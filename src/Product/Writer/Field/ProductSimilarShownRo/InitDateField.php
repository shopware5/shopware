<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductSimilarShownRo;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\DateField;

class InitDateField extends DateField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('initDate', 'init_date', 'product_similar_shown_ro', $constraintBuilder);
    }
}