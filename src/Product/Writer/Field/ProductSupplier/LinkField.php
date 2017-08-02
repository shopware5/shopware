<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductSupplier;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\StringField;

class LinkField extends StringField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('link', 'link', 'product_supplier', $constraintBuilder);
    }
}