<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductConfiguratorSet;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\AbstractField;

class PublicField extends AbstractField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('public', 'public', $constraintBuilder);
    }

}