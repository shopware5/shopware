<?php declare(strict_types=1);

namespace Shopware\Components\Events\Basket;

use Enlight_Event_EventArgs;
use sBasket;

class AddVoucherStartEvent extends Enlight_Event_EventArgs
{
    public const EVENT_NAME = 'Shopware_Modules_Basket_AddVoucher_Start';


    public function getSubject(): sBasket
    {
        return $this->get('subject');
    }


    public function getCode(): string
    {
        return $this->get('code');
    }


    public function getBasket(): string
    {
        return $this->get('basket');
    }
}