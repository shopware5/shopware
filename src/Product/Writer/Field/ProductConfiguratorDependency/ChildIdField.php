<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductConfiguratorDependency;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class ChildIdField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('childId', 'child_id', 'product_configurator_dependency', $constraintBuilder);
    }
}