<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductEsdSerial;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\StringField;

class SerialnumberField extends StringField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('serialnumber', 'serialnumber', $constraintBuilder);
    }

}