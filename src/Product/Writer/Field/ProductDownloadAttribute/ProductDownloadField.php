<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductDownloadAttribute;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\VirtualField;

class ProductDownloadField extends VirtualField
{
    public function __construct()
    {
        parent::__construct('productDownload', \Shopware\Product\Writer\Field\ProductDownloadAttribute\ProductDownloadUuidField::class);
    }
}