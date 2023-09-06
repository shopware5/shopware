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

namespace Shopware\Bundle\MailBundle\Service;

use Enlight_Components_Mail;
use League\Flysystem\FilesystemInterface;
use RuntimeException;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Models\Mail\Log;

class LogEntryMailBuilder implements LogEntryMailBuilderInterface
{
    public const INVALID_SENDER_REPLACEMENT_ADDRESS = 'invalid@example.com';

    private FilesystemInterface $filesystem;

    private MediaServiceInterface $mediaService;

    private Enlight_Components_Mail $mail;

    public function __construct(FilesystemInterface $filesystem, MediaServiceInterface $mediaService, Enlight_Components_Mail $mail)
    {
        $this->filesystem = $filesystem;
        $this->mediaService = $mediaService;
        $this->mail = $mail;
    }

    /**
     * {@inheritdoc}
     */
    public function build(Log $entry): Enlight_Components_Mail
    {
        $mail = clone $this->mail;

        try {
            $mail->setFrom($entry->getSender());
        } catch (RuntimeException $exception) {
            $mail->setFrom(self::INVALID_SENDER_REPLACEMENT_ADDRESS);
        }

        $entry->getRecipients()->map(function ($recipient) use ($mail) {
            $mail->addTo($recipient->getMailAddress());
        });

        if ($entry->getSubject() !== null) {
            $mail->setSubject($entry->getSubject());
        }

        if ($entry->getContentText() !== null) {
            $mail->setBodyText($entry->getContentText());
        }

        if ($entry->getContentHtml() !== null) {
            $mail->setBodyHtml($entry->getContentHtml());
        }

        if ($entry->getType() !== null) {
            $mail->setTemplateName($entry->getType()->getName());
        }

        if ($entry->getOrder() !== null) {
            $mail->setAssociation(LogEntryBuilder::ORDER_ASSOCIATION, $entry->getOrder());
        }

        if ($entry->getShop() !== null) {
            $mail->setAssociation(LogEntryBuilder::SHOP_ASSOCIATION, $entry->getShop());
        }

        $this->assignOrderDocuments($entry, $mail);
        $this->assignTemplateDocuments($entry, $mail);

        return $mail;
    }

    protected function assignOrderDocuments(Log $logEntry, Enlight_Components_Mail $mail): void
    {
        if ($logEntry->getDocuments()->isEmpty()) {
            return;
        }

        foreach ($logEntry->getDocuments() as $document) {
            $filePath = sprintf('documents/%s.pdf', $document->getHash());
            $fileName = sprintf('%s.pdf', $document->getType()->getName());

            if (!$this->filesystem->has($filePath)) {
                continue;
            }

            $fileAttachment = $mail->createAttachment(
                $this->filesystem->read($filePath)
            );
            $fileAttachment->filename = $fileName;
        }
    }

    protected function assignTemplateDocuments(Log $logEntry, Enlight_Components_Mail $mail): void
    {
        if ($logEntry->getType() === null || empty($logEntry->getType()->getAttachments())) {
            return;
        }

        $entryShopId = $logEntry->getShop() ? $logEntry->getShop()->getId() : null;
        $attachments = $logEntry->getType()->getAttachments();

        foreach ($attachments as $attachment) {
            $attachmentShopId = $attachment->getShopId();

            if ($attachmentShopId !== null && $attachmentShopId !== $entryShopId) {
                continue;
            }

            if (!$this->mediaService->has($attachment->getPath())) {
                continue;
            }

            $fileAttachment = $mail->createAttachment(
                $this->mediaService->read($attachment->getPath())
            );
            $fileAttachment->filename = $attachment->getFileName();
        }
    }
}
