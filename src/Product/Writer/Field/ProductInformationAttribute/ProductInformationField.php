<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductInformationAttribute;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\VirtualField;

class ProductInformationField extends VirtualField
{
    public function __construct()
    {
        parent::__construct('productInformation', \Shopware\Product\Writer\Field\ProductInformationAttribute\ProductInformationUuidField::class);
    }
}