<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductImg;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\IntField;

class MediaIdField extends IntField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('mediaId', 'media_id', $constraintBuilder);
    }

}