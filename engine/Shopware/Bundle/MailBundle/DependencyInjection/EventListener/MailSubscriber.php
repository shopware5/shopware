<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\MailBundle\DependencyInjection\EventListener;

use Enlight_Event_EventArgs;
use Shopware\Bundle\MailBundle\Service\LogServiceInterface;

class MailSubscriber implements \Enlight\Event\SubscriberInterface
{
    /**
     * @var LogServiceInterface
     */
    private $logService;

    /**
     * @var bool
     */
    private $active;

    public function __construct(LogServiceInterface $logService, bool $active)
    {
        $this->logService = $logService;
        $this->active = $active;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Enlight_Components_Mail_Send' => 'onSendMail',
        ];
    }

    public function onSendMail(Enlight_Event_EventArgs $args): void
    {
        if (!$this->active) {
            return;
        }

        $this->logService->log($args->get('mail'));
    }
}
