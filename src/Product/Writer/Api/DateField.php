<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Api;

use Shopware\Framework\Validation\ConstraintBuilder;

class DateField extends WritableField
{
    public function __construct(string $name, string $storageName, string $tableName, ConstraintBuilder $constraintBuilder)
    {
        parent::__construct(
            $name,
            $storageName,
            $tableName,
            $constraintBuilder->isNotBlank()->isDate()->getConstraints(),
            $constraintBuilder->isDate()->getConstraints()
        );
    }

    public function getValueTransformer()
    {
        return new ValueTransformerDate();
    }


}