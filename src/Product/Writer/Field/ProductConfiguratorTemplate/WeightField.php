<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductConfiguratorTemplate;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\AbstractField;

class WeightField extends AbstractField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('weight', 'weight', $constraintBuilder);
    }

}