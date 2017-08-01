<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductAvoidCustomergroup;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class CustomergroupIdField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('customergroupId', 'customergroup_id', $constraintBuilder);
    }

}