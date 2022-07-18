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

namespace Shopware\Tests\Functional\Components;

use Enlight_Components_Mail;
use PHPUnit\Framework\TestCase;
use Shopware\Tests\Functional\Traits\ContainerTrait;

class MailTest extends TestCase
{
    use ContainerTrait;

    public function testMessageId(): void
    {
        $mailTransport = $this->getContainer()->get('mailtransport');

        $mail = new Enlight_Components_Mail(null, 'test.com');
        $mail->setBodyText('Test Hello');
        $mail->addTo('test@example.com');

        $mail->send($mailTransport);

        $headers = $mail->getHeaders();
        static::assertArrayHasKey('Message-Id', $headers);
        $messageId = $headers['Message-Id'][0];
        static::assertIsString($messageId);
        static::assertStringContainsString('@test.com', $messageId);
        static::assertStringStartsWith('<', $messageId);
        static::assertStringEndsWith('>', $messageId);
    }
}
