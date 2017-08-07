<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductVote;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\DateDefaultCreateField;

class CreatedAtField extends DateDefaultCreateField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('createdAt', 'created_at', 'product_vote', $constraintBuilder);
    }
}