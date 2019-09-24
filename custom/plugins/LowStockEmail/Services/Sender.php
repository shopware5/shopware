<?php

namespace LowStockEmail\Services;

use Enlight_Components_Mail;
use Smarty;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Sender
{
    /**
     * @var Smarty
     */
    private $smarty;
    /**
     * @var Enlight_Components_Mail|object
     */
    private $mailer;

    public function __construct(ContainerInterface $container)
    {
        $this->mailer = $container->get('mail');
        $this->smarty = new Smarty;
    }

    public function send(array $config): void
    {
        $this->smarty->assign('qty', $config['low_stock_qty']);
        $this->smarty->assign('product_count', $config['product_count']);
        $this->mailer
            ->setFrom('ShopWare5-Bot')
            ->setSubject('Low stock alert!')
            ->addTo($config['recipient'])
            ->setBodyHtml($this->smarty->fetch(__DIR__ . '/../Resources/views/' . $config['template'] ?? 'default.tpl'))
            ->send();
    }
}
