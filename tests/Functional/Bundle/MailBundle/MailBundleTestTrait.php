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

namespace Shopware\Tests\Functional\Bundle\MailBundle;

use Enlight_Components_Mail;

trait MailBundleTestTrait
{
    public function createSimpleMail(): Enlight_Components_Mail
    {
        $mail = new Enlight_Components_Mail('UTF-8');

        $subject = 'This is a subject';
        $sender = 'shopware@example.com';
        $recipients = [];
        $contentText = 'Lorem ipsum dolor sit amet.';

        for ($i = 0; $i < 3; ++$i) {
            $recipients[] = sprintf('test-%d@example.com', $i);
        }

        $mail->setSubject($subject);
        $mail->setFrom($sender);
        $mail->addTo($recipients);
        $mail->setBodyText($contentText);

        return $mail;
    }
}
