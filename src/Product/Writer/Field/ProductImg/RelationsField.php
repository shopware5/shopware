<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\ProductImg;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\TextField;

class RelationsField extends TextField
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('relations', 'relations', 'product_img', $constraintBuilder);
    }
}