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

use Enlight_Components_Mail;
use League\Flysystem\FilesystemInterface;
use Shopware\Models\Mail\Contact;
use Shopware\Models\Mail\Log;
use Shopware\Models\Order\Document\Document;
use Zend_Mime;
use Zend_Mime_Part;

class LogEntryMailBuilder implements LogEntryMailBuilderInterface
{
    public const INVALID_SENDER_REPLACEMENT_ADDRESS = 'invalid@example.com';

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function build(Log $entry): Enlight_Components_Mail
    {
        $mail = new Enlight_Components_Mail('UTF-8');

        try {
            $mail->setFrom($entry->getSender());
        } catch (\RuntimeException $exception) {
            $mail->setFrom($this::INVALID_SENDER_REPLACEMENT_ADDRESS);
        }

        $entry->getRecipients()->map(function ($recipient) use ($mail) {
            /* @var Contact $recipient */
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

        if (count($entry->getDocuments()) < 1) {
            return $mail;
        }

        /** @var Document $document */
        foreach ($entry->getDocuments() as $document) {
            $filePath = sprintf('documents/%s.pdf', $document->getHash());
            $fileName = sprintf('%s.pdf', $document->getType()->getName());

            if (!$this->filesystem->has($filePath)) {
                continue;
            }

            $mail->addAttachment($this->createAttachment($filePath, $fileName));
        }

        return $mail;
    }

    protected function createAttachment(string $filePath, string $fileName): Zend_Mime_Part
    {
        $content = $this->filesystem->read($filePath);
        $zendAttachment = new Zend_Mime_Part($content);
        $zendAttachment->type = 'application/pdf';
        $zendAttachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
        $zendAttachment->encoding = Zend_Mime::ENCODING_BASE64;
        $zendAttachment->filename = $fileName;

        return $zendAttachment;
    }
}
