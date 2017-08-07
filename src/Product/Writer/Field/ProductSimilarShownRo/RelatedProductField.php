<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductSimilarShownRo;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\VirtualField;

class RelatedProductField extends VirtualField
{
    public function __construct()
    {
        parent::__construct('relatedProduct', \Shopware\Product\Writer\Field\ProductSimilarShownRo\RelatedProductUuidField::class);
    }
}