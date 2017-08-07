<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductVote;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\StringField;

class EmailField extends StringField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('email', 'email', 'product_vote', $constraintBuilder);
    }
}