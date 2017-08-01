<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Api;


use Shopware\Framework\Validation\ConstraintBuilder;

class IntField extends Field
{
    public function __construct(string $name, string $storageName, ConstraintBuilder $constraintBuilder)
    {
        parent::__construct(
            $name,
            $storageName,
            $constraintBuilder->isNotBlank()->isInt()->getConstraints(),
            $constraintBuilder->isInt()->getConstraints()
        );
    }
}