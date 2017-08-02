<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductTranslation;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\HtmlTextField;

class DescriptionLongField extends HtmlTextField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('descriptionLong', 'description_long', 'product_translation', $constraintBuilder);
    }
}