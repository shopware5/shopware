<?php declare(strict_types=1);

namespace Shopware\Core\Events\Basket;

use Enlight_Event_EventArgs;
use sBasket;

class BeforeAddOrderSurchargePercentEvent extends Enlight_Event_EventArgs
{
    public const EVENT_NAME = 'Shopware_Modules_Basket_BeforeAddOrderSurchargePercent';


    public function getSubject(): sBasket
    {
        return $this->get('subject');
    }


    public function getSurcharge(): array
    {
        return $this->get('surcharge');
    }
}