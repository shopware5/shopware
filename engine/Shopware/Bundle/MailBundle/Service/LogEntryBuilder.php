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

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Enlight_Components_Mail;
use PDO;
use Shopware\Models\Mail\Contact;
use Shopware\Models\Mail\Log;
use Shopware\Models\Mail\Mail;
use Shopware\Models\Mail\Repository as MailRepository;
use Shopware\Models\Order\Document\Document;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Repository as OrderRepository;
use Shopware\Models\Shop\Shop;
use Zend_Mime_Part;

class LogEntryBuilder implements LogEntryBuilderInterface
{
    public const ORDER_ASSOCIATION = 'order';
    public const ORDER_ID_ASSOCIATION = 'orderId';
    public const ORDER_NUMBER_ASSOCIATION = 'orderNumber';
    public const SHOP_ASSOCIATION = 'shop';
    public const SHOP_ID_ASSOCIATION = 'shopId';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MailRepository
     */
    private $mailRepository;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->mailRepository = $entityManager->getRepository(Mail::class);
        $this->orderRepository = $entityManager->getRepository(Order::class);
    }

    /**
     * {@inheritdoc}
     */
    public function build(Enlight_Components_Mail $mail): Log
    {
        $logEntry = new Log();

        $logEntry->setSubject(iconv_mime_decode($mail->getSubject()));
        $logEntry->setSender($mail->getFrom());
        $logEntry->setSentAt(new DateTime($mail->getDate()));
        $logEntry->setContentText($mail->getPlainBodyText());

        if ($mail->getBodyHtml() instanceof Zend_Mime_Part) {
            $logEntry->setContentHtml($mail->getBodyHtml()->getRawContent());
        }

        $this->assignType($logEntry, $mail->getTemplateName());
        $this->assignOrder($logEntry, $mail);
        $this->assignShop($logEntry, $mail);

        $this->assignRecipients($logEntry, array_map('trim', $mail->getRecipients()));
        $this->assignDocuments($logEntry, $mail);

        return $logEntry;
    }

    protected function assignType(Log $logEntry, ?string $templateName): void
    {
        if (empty($templateName)) {
            return;
        }

        $type = $this->mailRepository->findOneBy(['name' => $templateName]);

        if ($type instanceof Mail) {
            $logEntry->setType($type);
        }
    }

    protected function assignOrder(Log $logEntry, Enlight_Components_Mail $mail): void
    {
        /** @var Order|null $order */
        $order = $this->resolveOrderByAssociation($mail);

        if ($order !== null) {
            $logEntry->setOrder($order);

            return;
        }

        /** @var Order|null $order */
        $order = $this->resolveOrderByType($logEntry->getType());

        if ($order !== null) {
            $logEntry->setOrder($order);
        }
    }

    protected function assignShop(Log $logEntry, Enlight_Components_Mail $mail): void
    {
        if ($mail->getAssociation(self::SHOP_ASSOCIATION) !== null) {
            $logEntry->setShop($mail->getAssociation(self::SHOP_ASSOCIATION));

            return;
        }

        if ($mail->getAssociation(self::SHOP_ID_ASSOCIATION) !== null) {
            /** @var Shop $shop */
            $shop = $this->entityManager->getPartialReference(
                Shop::class,
                $mail->getAssociation(self::SHOP_ID_ASSOCIATION)
            );

            $logEntry->setShop($shop);

            return;
        }

        if ($logEntry->getOrder() !== null && $logEntry->getOrder()->getLanguageSubShop() !== null) {
            $logEntry->setShop($logEntry->getOrder()->getLanguageSubShop());
        }
    }

    protected function resolveOrderByAssociation(Enlight_Components_Mail $mail): ?Order
    {
        $order = $mail->getAssociation(self::ORDER_ASSOCIATION);

        if ($order instanceof Order) {
            return $order;
        }

        $orderId = $mail->getAssociation(self::ORDER_ID_ASSOCIATION);

        if ($orderId !== null) {
            $order = $this->entityManager->getPartialReference(
                Order::class,
                $orderId
            );

            if ($order instanceof Order) {
                return $order;
            }
        }

        $orderNumber = $mail->getAssociation(self::ORDER_NUMBER_ASSOCIATION);

        if ($orderNumber !== null) {
            $order = $this->orderRepository->findOneBy(['number' => $orderNumber]);

            if ($order instanceof Order) {
                return $order;
            }
        }

        return null;
    }

    protected function resolveOrderByType(?Mail $type): ?Order
    {
        if ($type === null || empty($type->getContext())) {
            return null;
        }

        $context = $type->getContext();

        if (isset($context['sOrder']['orderID'])) {
            $order = $this->entityManager->getPartialReference(
                Order::class,
                $context['sOrder']['orderID']
            );

            if ($order instanceof Order) {
                return $order;
            }
        }

        if (isset($context['sOrderNumber'])) {
            $order = $this->orderRepository->findOneBy(['number' => $context['sOrderNumber']]);

            if ($order instanceof Order) {
                return $order;
            }
        }

        return null;
    }

    protected function assignRecipients(Log $logEntry, array $recipients = []): void
    {
        $knownRecipients = $this->getKnownRecipients($recipients);
        $unknownRecipients = array_flip($recipients);

        $associatedContacts = [];

        foreach ($knownRecipients as $recipient) {
            unset($unknownRecipients[$recipient['mail_address']]);

            $associatedContacts[] = $this->entityManager->getPartialReference(
                Contact::class,
                $recipient['id']
            );
        }

        foreach (array_keys($unknownRecipients) as $recipient) {
            $contact = new Contact();
            $contact->setMailAddress($recipient);

            $this->persistContact($contact);

            $associatedContacts[] = $this->entityManager->getPartialReference(
                Contact::class,
                $contact->getId()
            );
        }

        $logEntry->setRecipients(new ArrayCollection($associatedContacts));
    }

    protected function getKnownRecipients(array $recipients): array
    {
        $qb = $this->entityManager->getConnection()->createQueryBuilder();
        $tableName = $this->entityManager->getClassMetadata(Contact::class)->getTableName();

        $qb->select('*')
            ->from($tableName)
            ->add('where', $qb->expr()->in('mail_address', ':recipients'))
            ->setParameter('recipients', $recipients, Connection::PARAM_STR_ARRAY);

        return $qb->execute()->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function persistContact(Contact $contact): void
    {
        $this->entityManager->persist($contact);
        $this->entityManager->flush();
    }

    protected function assignDocuments(Log $logEntry, Enlight_Components_Mail $mail): void
    {
        if (!$mail->hasAttachments || $logEntry->getOrder() === null) {
            return;
        }

        $orderRepository = $this->entityManager->getRepository(Order::class);
        $documents = $orderRepository->getDocuments([$logEntry->getOrder()->getId()]);
        $filenameIdMap = [];

        foreach ($documents as $document) {
            $filename = $this->getDocumentFilename($document);

            if ($filename) {
                $filenameIdMap[$filename] = $document['id'];
            }
        }

        /** @var Zend_Mime_Part $part */
        foreach ($mail->getParts() as $part) {
            if (isset($filenameIdMap[$part->filename])) {
                /** @var Document $document */
                $document = $this->entityManager->getPartialReference(
                    Document::class,
                    $filenameIdMap[$part->filename]
                );
                $logEntry->addDocument($document);
            }
        }
    }

    protected function getDocumentFilename(array $document): ?string
    {
        if (!isset($document['type']['name'])) {
            return null;
        }

        return sprintf('%s.pdf', $document['type']['name']);
    }
}
