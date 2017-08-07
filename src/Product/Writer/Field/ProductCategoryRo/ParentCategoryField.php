<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductCategoryRo;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\VirtualField;

class ParentCategoryField extends VirtualField
{
    public function __construct()
    {
        parent::__construct('parentCategory', \Shopware\Product\Writer\Field\ProductCategoryRo\ParentCategoryUuidField::class);
    }
}