<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductAttribute;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\VirtualField;

class ProductDetailField extends VirtualField
{
    public function __construct()
    {
        parent::__construct('productDetail', \Shopware\Product\Writer\Field\ProductAttribute\ProductDetailUuidField::class);
    }
}