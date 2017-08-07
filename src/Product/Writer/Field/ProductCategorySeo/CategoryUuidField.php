<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductCategorySeo;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\ReferenceField;

class CategoryUuidField extends ReferenceField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('categoryUuid', 'category_uuid', 'product_category_seo', $constraintBuilder);
    }
}