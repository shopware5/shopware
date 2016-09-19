<?php
namespace Shopware\Components;

use Enlight\Event\SubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BccSubscriber implements SubscriberInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Components_Mail_Send' => 'onMailSend'
        ];
    }

    public function onMailSend(\Enlight_Event_EventArgs $args)
    {
        /**
         * Ignore if config is not initialized
         */
        if (!$this->container->has('config')) {
            return;
        }

        $bccs = $this->container->get('config')->get('mailBcc');

        /** @var \Enlight_Components_Mail $mail */
        $mail = $args->get('mail');

        if (!empty($bccs)) {
            $bccs = array_map('trim', explode(',', $bccs));

            $mail->addBcc($bccs);
        }
    }
}
