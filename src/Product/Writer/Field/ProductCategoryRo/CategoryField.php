<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductCategoryRo;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\VirtualField;

class CategoryField extends VirtualField
{
    public function __construct()
    {
        parent::__construct('category', \Shopware\Product\Writer\Field\ProductCategoryRo\CategoryUuidField::class);
    }
}