<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductTranslation;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\VirtualField;

class LanguageField extends VirtualField
{
    public function __construct()
    {
        parent::__construct('language', \Shopware\Product\Writer\Field\ProductTranslation\LanguageUuidField::class);
    }
}