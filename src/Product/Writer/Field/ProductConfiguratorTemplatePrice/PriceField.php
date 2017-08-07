<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductConfiguratorTemplatePrice;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\FloatField;

class PriceField extends FloatField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('price', 'price', 'product_configurator_template_price', $constraintBuilder);
    }
}