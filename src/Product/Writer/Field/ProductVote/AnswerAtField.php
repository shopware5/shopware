<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductVote;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\DateField;

class AnswerAtField extends DateField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('answerAt', 'answer_at', 'product_vote', $constraintBuilder);
    }
}