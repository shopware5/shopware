<?php

declare(strict_types=1);
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

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\MailBundle\Service\LogEntryBuilder;
use Shopware\Bundle\MailBundle\Service\LogEntryBuilderInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Mail\Contact;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class LogEntryBuilderTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;
    use MailBundleTestTrait;

    private const TEST_MAIL = 'foo@test.com';

    private LogEntryBuilderInterface $builder;

    private ModelManager $modelManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->modelManager = $this->getContainer()->get(ModelManager::class);
        $this->builder = new LogEntryBuilder($this->modelManager);
    }

    public function testBuildWithSimpleMail(): void
    {
        $mail = $this->createSimpleMail();
        $log = $this->builder->build($mail);

        static::assertSame($mail->getSubject(), $log->getSubject());
        static::assertSame($mail->getFrom(), $log->getSender());
        static::assertInstanceOf(Contact::class, $log->getRecipients()->first());
        static::assertSame($mail->getPlainBodyText(), $log->getContentText());
    }

    public function testBuildWithDetachedRecipient(): void
    {
        $contact = new Contact();
        $contact->setMailAddress(self::TEST_MAIL);
        $this->modelManager->persist($contact);
        $this->modelManager->flush($contact);
        $this->modelManager->detach($contact);

        $mail = $this->createSimpleMail();
        $mail->clearRecipients();
        $mail->addTo(self::TEST_MAIL);

        $recipient = $this->builder->build($mail)->getRecipients()->first();
        static::assertInstanceOf(Contact::class, $recipient);
        static::assertSame(self::TEST_MAIL, $recipient->getMailAddress());
    }
}
