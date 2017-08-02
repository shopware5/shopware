<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductDownload;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\FloatField;

class SizeField extends FloatField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('size', 'size', 'product_download', $constraintBuilder);
    }
}