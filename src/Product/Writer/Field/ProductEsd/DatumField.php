<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductEsd;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\DateField;

class DatumField extends DateField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('datum', 'datum', $constraintBuilder);
    }

}