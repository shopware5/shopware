<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class ActiveField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('active', 'active', $constraintBuilder);
    }

}