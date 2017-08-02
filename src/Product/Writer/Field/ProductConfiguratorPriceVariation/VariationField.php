<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductConfiguratorPriceVariation;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\FloatField;

class VariationField extends FloatField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('variation', 'variation', 'product_configurator_price_variation', $constraintBuilder);
    }
}