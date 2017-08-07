<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductEsdAttribute;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\VirtualField;

class ProductEsdField extends VirtualField
{
    public function __construct()
    {
        parent::__construct('productEsd', \Shopware\Product\Writer\Field\ProductEsdAttribute\ProductEsdUuidField::class);
    }
}