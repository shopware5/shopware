<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductVote;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\ReferenceField;

class ShopUuidField extends ReferenceField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('shopUuid', 'shop_uuid', 'product_vote', $constraintBuilder);
    }
}