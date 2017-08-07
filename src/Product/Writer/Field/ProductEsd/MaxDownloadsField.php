<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductEsd;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class MaxDownloadsField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('maxDownloads', 'max_downloads', 'product_esd', $constraintBuilder);
    }
}