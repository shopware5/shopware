<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductInformationAttribute;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class InformationIdField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('informationId', 'information_id', 'product_information_attribute', $constraintBuilder);
    }
}