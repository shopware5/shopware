<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductImgAttribute;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\StringField;

class Attribute2Field extends StringField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('attribute2', 'attribute2', 'product_img_attribute', $constraintBuilder);
    }
}