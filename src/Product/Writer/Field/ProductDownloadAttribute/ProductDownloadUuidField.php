<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductDownloadAttribute;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\ReferenceField;

class ProductDownloadUuidField extends ReferenceField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('productDownloadUuid', 'product_download_uuid', 'product_download_attribute', $constraintBuilder);
    }
}