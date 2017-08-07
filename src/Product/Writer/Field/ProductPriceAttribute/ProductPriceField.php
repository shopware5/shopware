<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductPriceAttribute;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\VirtualField;

class ProductPriceField extends VirtualField
{
    public function __construct()
    {
        parent::__construct('productPrice', \Shopware\Product\Writer\Field\ProductPriceAttribute\ProductPriceUuidField::class);
    }
}