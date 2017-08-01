<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductNotification;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\StringField;

class LanguageField extends StringField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('language', 'language', $constraintBuilder);
    }

}