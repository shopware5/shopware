<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductEsdAttribute;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class EsdIdField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('esdId', 'esd_id', $constraintBuilder);
    }

}