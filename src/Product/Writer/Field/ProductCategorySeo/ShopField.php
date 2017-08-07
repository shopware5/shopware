<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductCategorySeo;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\VirtualField;

class ShopField extends VirtualField
{
    public function __construct()
    {
        parent::__construct('shop', \Shopware\Product\Writer\Field\ProductCategorySeo\ShopUuidField::class);
    }
}