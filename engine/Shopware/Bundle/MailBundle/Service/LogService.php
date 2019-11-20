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

use Doctrine\DBAL\Connection;
use Enlight_Components_Mail;
use Shopware\Bundle\MailBundle\Service\Filter\AdministrativeMailFilter;
use Shopware\Bundle\MailBundle\Service\Filter\MailFilterInterface;
use Shopware\Models\Mail\Contact;
use Shopware\Models\Mail\Log;
use Shopware\Models\Order\Document\Document;

class LogService implements LogServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

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

    public function __construct(Connection $connection, LogEntryBuilderInterface $entryBuilder, iterable $filters)
    {
        $this->connection = $connection;
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

        $this->connection->beginTransaction();

        try {
            $contacts = $this->loadContacts();

            foreach ($this->entries as $entry) {
                $this->connection->insert('s_mail_log', [
                    'type_id' => $entry->getType() ? $entry->getType()->getId() : null,
                    'order_id' => $entry->getOrder() ? $entry->getOrder()->getId() : null,
                    'shop_id' => $entry->getShop() ? $entry->getShop()->getId() : null,
                    'subject' => $entry->getSubject(),
                    'sender' => $entry->getSender(),
                    'sent_at' => $entry->getSentAt()->format('Y-m-d H:i:s'),
                    'content_html' => $entry->getContentHtml(),
                    'content_text' => $entry->getContentText(),
                ]);
                $mailLogId = (int) $this->connection->lastInsertId();

                /** @var Document $document */
                foreach ($entry->getDocuments() as $document) {
                    $this->connection->insert('s_mail_log_document', [
                        'log_id' => $mailLogId,
                        'document_id' => $document->getId(),
                    ]);
                }

                /** @var Contact $recipient */
                foreach ($entry->getRecipients() as $recipient) {
                    $mail = mb_strtolower(trim($recipient->getMailAddress()));

                    $this->connection->insert('s_mail_log_recipient', [
                        'log_id' => $mailLogId,
                        'contact_id' => $recipient->getId() ?: $contacts[$mail],
                    ]);
                }
            }

            $this->connection->commit();
        } catch (\Exception $exception) {
            $this->connection->rollback();
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

    private function loadContacts(): array
    {
        $recipients = [];

        foreach ($this->entries as $entry) {
            /** @var Contact $recipient */
            foreach ($entry->getRecipients() as $recipient) {
                if ($recipient->getId()) {
                    continue;
                }

                $recipients[] = mb_strtolower($recipient->getMailAddress());
            }
        }

        $recipients = array_unique(array_filter(array_map('trim', $recipients)));

        $sql = 'SELECT LOWER(mail_address), id FROM s_mail_log_contact WHERE mail_address IN (:addresses)';
        $foundRecipients = $this->connection->executeQuery($sql, ['addresses' => $recipients])->fetchAll(\PDO::FETCH_KEY_PAIR);

        foreach ($recipients as $recipient) {
            if (array_key_exists($recipient, $foundRecipients)) {
                continue;
            }

            $this->connection->insert('s_mail_log_contact', ['mail_address' => $recipient]);
            $foundRecipients[$recipient] = (int) $this->connection->lastInsertId();
        }

        return $foundRecipients;
    }
}
