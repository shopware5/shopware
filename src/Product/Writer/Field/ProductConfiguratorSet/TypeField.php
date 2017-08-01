<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductConfiguratorSet;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class TypeField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('type', 'type', $constraintBuilder);
    }

}