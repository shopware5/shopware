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

namespace Shopware\Bundle\MailBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Enlight_Components_Mail;
use Shopware\Bundle\MailBundle\Service\Filter\AdministrativeMailFilter;
use Shopware\Bundle\MailBundle\Service\Filter\MailFilterInterface;
use Shopware\Models\Mail\Log;

class LogService implements LogServiceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LogEntryBuilderInterface
     */
    private $entryBuilder;

    /**
     * @var Log[]|array
     */
    private $entries;

    /**
     * @var MailFilterInterface[]
     */
    private $filters;

    /**
     * @var bool
     */
    private $flushError = false;

    public function __construct(EntityManagerInterface $entityManager, LogEntryBuilderInterface $entryBuilder, iterable $filters)
    {
        $this->entityManager = $entityManager;
        $this->entryBuilder = $entryBuilder;
        $this->filters = $filters;
        $this->entries = [];
    }

    /**
     * {@inheritdoc}
     */
    public function log(Enlight_Components_Mail $mail): void
    {
        foreach ($this->filters as $filter) {
            if ($filter->filter($mail) === null) {
                return;
            }
        }

        $this->entries[] = $this->entryBuilder->build($mail);
        $this->handleErrorMail($mail);
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
        if (empty($this->entries) || $this->flushError) {
            return;
        }

        $this->entityManager->clear();
        $this->entityManager->beginTransaction();

        try {
            while (count($this->entries) > 0) {
                $this->entityManager->persist(array_pop($this->entries));

                if (count($this->entries) % 20 === 0) {
                    $this->entityManager->flush();
                }
            }

            $this->entityManager->commit();
        } catch (\Exception $exception) {
            $this->entityManager->rollback();
            throw $exception;
        }
    }

    /**
     * The error logger will in some cases try to send an e-mail when an uncaught exception occurs.
     * E-Mails are sent via register_shutdown_function in this case and therefore after the
     * KernelEvents::TERMINATE Event, so we have to make sure flush() is called anyway.
     */
    protected function handleErrorMail(Enlight_Components_Mail $mail): void
    {
        if ($mail->getAssociation(AdministrativeMailFilter::ADMINISTRATIVE_MAIL)) {
            try {
                $this->flush();
            } catch (\Exception $exception) {
                /*
                 * flush() could throw exceptions, which would otherwise be caught by Monolog again.
                 * This is a precaution to prevent an infinite loop.
                 */
                $this->flushError = true;
            }
        }
    }
}
