<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductEsd;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class SerialsField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('serials', 'serials', $constraintBuilder);
    }

}