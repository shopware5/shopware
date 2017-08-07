<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductConfiguratorTemplate;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class StockminField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('stockmin', 'stockmin', 'product_configurator_template', $constraintBuilder);
    }
}