<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductTranslation;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\StringField;

class Attr2Field extends StringField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('attr2', 'attr2', 'product_translation', $constraintBuilder);
    }
}