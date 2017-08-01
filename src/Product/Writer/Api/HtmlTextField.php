<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Api;

use Shopware\Framework\Validation\ConstraintBuilder;

class HtmlTextField extends Field
{
    public function __construct(string $name, string $storageName, ConstraintBuilder $constraintBuilder)
    {
        parent::__construct(
            $name,
            $storageName,
            $constraintBuilder->isNotBlank()->isString()->getConstraints(),
            $constraintBuilder->isString()->getConstraints()
        );
    }

}