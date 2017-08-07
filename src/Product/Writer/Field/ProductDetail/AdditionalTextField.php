<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductDetail;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\StringField;

class AdditionalTextField extends StringField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('additionalText', 'additional_text', 'product_detail', $constraintBuilder);
    }
}