<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\Product;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\VirtualField;

class SupplierField extends VirtualField
{
    public function __construct()
    {
        parent::__construct('supplier', \Shopware\Product\Writer\Field\Product\SupplierUuidField::class);
    }
}