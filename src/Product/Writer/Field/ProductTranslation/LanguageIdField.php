<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductTranslation;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class LanguageIdField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('languageId', 'language_id', 'product_translation', $constraintBuilder);
    }
}