<?php

namespace Shopware\PaymentMethod\Gateway;

use Shopware\Context\Struct\TranslationContext;
use Shopware\PaymentMethod\Struct\PaymentMethodCollection;

class PaymentMethodRepository
{
    /**
     * @var PaymentMethodReader
     */
    private $reader;

    public function __construct(PaymentMethodReader $reader)
    {
        $this->reader = $reader;
    }

    public function read(array $ids, TranslationContext $context): PaymentMethodCollection
    {
        return $this->reader->read($ids, $context);
    }

}