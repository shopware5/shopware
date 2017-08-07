<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductTranslation;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\ReferenceField;

class LanguageUuidField extends ReferenceField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('languageUuid', 'language_uuid', 'product_translation', $constraintBuilder);
    }
}