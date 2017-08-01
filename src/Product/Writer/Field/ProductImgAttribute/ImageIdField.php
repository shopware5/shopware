<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductImgAttribute;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class ImageIdField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('imageId', 'image_id', $constraintBuilder);
    }

}