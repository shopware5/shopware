<?php declare(strict_types=1);

namespace Shopware\Product\Writer;

use Shopware\Framework\ShopwareException;

class ExceptionInvalidFieldValueType extends \InvalidArgumentException implements ShopwareException
{

}