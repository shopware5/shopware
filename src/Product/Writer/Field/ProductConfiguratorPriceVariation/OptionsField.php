<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductConfiguratorPriceVariation;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\TextField;

class OptionsField extends TextField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('options', 'options', 'product_configurator_price_variation', $constraintBuilder);
    }
}