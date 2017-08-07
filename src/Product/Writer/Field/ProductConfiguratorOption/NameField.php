<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductConfiguratorOption;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\StringField;

class NameField extends StringField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('name', 'name', 'product_configurator_option', $constraintBuilder);
    }
}