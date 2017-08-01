<?php declare(strict_types=1);

namespace Shopware\Product\Writer;

use Shopware\Framework\ShopwareException;

class ExceptionNoUpdatedRecord extends \InvalidArgumentException implements ShopwareException
{

}