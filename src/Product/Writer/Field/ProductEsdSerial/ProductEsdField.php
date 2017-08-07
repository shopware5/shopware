<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductEsdSerial;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\VirtualField;

class ProductEsdField extends VirtualField
{
    public function __construct()
    {
        parent::__construct('productEsd', \Shopware\Product\Writer\Field\ProductEsdSerial\ProductEsdUuidField::class);
    }
}