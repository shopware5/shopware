<?php declare(strict_types=1);

namespace Shopware\Core\Events\Basket;

use Enlight_Event_EventArgs;
use sBasket;

class DeleteArticleStartEvent extends Enlight_Event_EventArgs
{
    public const EVENT_NAME = 'Shopware_Modules_Basket_DeleteArticle_Start';

    public function getSubject(): sBasket
    {
        return $this->get('subject');
    }


    public function getId(): int
    {
        return $this->get('id');
    }
}