<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\MailBundle\DependencyInjection\EventListener;

use Shopware\Bundle\MailBundle\Service\LogServiceInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\HttpKernel\KernelEvents;

class TerminationSubscriber implements \Enlight\Event\SubscriberInterface
{
    /**
     * @var LogServiceInterface
     */
    private $logService;

    /**
     * @var bool
     */
    private $active;

    public function __construct(LogServiceInterface $logservice, bool $active)
    {
        $this->logService = $logservice;
        $this->active = $active;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => 'onTermination',
            ConsoleEvents::TERMINATE => 'onTermination',
        ];
    }

    public function onTermination(): void
    {
        if (!$this->active) {
            return;
        }

        $this->logService->flush();
    }
}
