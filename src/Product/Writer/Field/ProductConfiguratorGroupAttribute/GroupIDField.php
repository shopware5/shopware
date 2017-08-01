<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductConfiguratorGroupAttribute;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class GroupIDField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('groupID', 'groupID', $constraintBuilder);
    }

}