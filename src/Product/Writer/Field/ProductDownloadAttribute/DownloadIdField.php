<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductDownloadAttribute;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class DownloadIdField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('downloadId', 'download_id', 'product_download_attribute', $constraintBuilder);
    }
}