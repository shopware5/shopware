<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Api;

use Shopware\Framework\Validation\ConstraintBuilder;

class StringField extends WritableField
{
    public function __construct(string $name, string $storageName, string $tableName, ConstraintBuilder $constraintBuilder)
    {
        parent::__construct(
            $name,
            $storageName,
            $tableName,
            $constraintBuilder->isNotBlank()->isString()->getConstraints(),
            $constraintBuilder->isString()->isShorterThen(255)->getConstraints()
        );
    }

    public function getFilters()
    {
        return [
            new HtmlFilter(),
        ];
    }
}