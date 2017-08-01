<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductConfiguratorDependency;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class ParentIdField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('parentId', 'parent_id', $constraintBuilder);
    }

}