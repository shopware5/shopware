<?php declare(strict_types=1);

namespace Shopware\Core\Events\Basket;

use Enlight_Event_EventArgs;

class GetBasketAllowEmptyBasketEvent extends Enlight_Event_EventArgs
{
    public const EVENT_NAME = 'Shopware_Modules_Basket_sGetBasket_AllowEmptyBasket';


    public function getArticles(): array
    {
        return $this->get('articles');
    }


    public function getTotalAmount(): float
    {
        return $this->get('totalAmount');
    }


    public function getTotalAmountWithTax(): float
    {
        return $this->get('totalAmountWithTax');
    }


    public function getTotalCount(): int
    {
        return $this->get('totalCount');
    }


    public function getTotalAmountNet(): float
    {
        return $this->get('totalAmountNet');
    }
}