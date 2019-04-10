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

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Enlight\Event\SubscriberInterface;
use Shopware\Models\Mail\Log;
use Shopware\Models\Mail\LogRepositoryInterface;

class MailLogCleanupCronSubscriber implements SubscriberInterface
{
    /**
     * @var LogRepositoryInterface
     */
    private $logRepository;

    /**
     * @var int
     */
    private $maximumAgeInDays;

    public function __construct(EntityManagerInterface $entityManager, int $maximumAgeInDays)
    {
        $this->logRepository = $entityManager->getRepository(Log::class);
        $this->maximumAgeInDays = $maximumAgeInDays > 0 ? $maximumAgeInDays : 0;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Shopware_Cronjob_MailLogCleanup' => 'mailLogCleanup',
        ];
    }

    public function mailLogCleanup(): void
    {
        $now = new DateTime('now');
        $maximumAgeInterval = new DateInterval(sprintf('P%dD', $this->maximumAgeInDays));

        $this->logRepository->deleteByDate(null, $now->sub($maximumAgeInterval));
    }
}
