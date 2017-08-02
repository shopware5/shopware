<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductImgMapping;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class ImageIdField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('imageId', 'image_id', 'product_img_mapping', $constraintBuilder);
    }
}