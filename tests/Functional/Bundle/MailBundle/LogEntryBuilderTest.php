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

use Doctrine\ORM\EntityManagerInterface;
use Shopware\Bundle\MailBundle\Service\LogEntryBuilder;
use Shopware\Bundle\MailBundle\Service\LogEntryBuilderInterface;
use Shopware\Models\Mail\Contact;

class LogEntryBuilderTest extends \PHPUnit\Framework\TestCase
{
    use MailBundleTestTrait;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LogEntryBuilderInterface
     */
    private $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = Shopware()->Container()->get('models');
        $this->builder = new LogEntryBuilder($this->entityManager);
    }

    public function testBuildWithSimpleMail(): void
    {
        $mail = $this->createSimpleMail();
        $entry = $this->builder->build($mail);

        static::assertNotNull($entry);

        static::assertEquals($mail->getSubject(), $entry->getSubject());
        static::assertEquals($mail->getFrom(), $entry->getSender());
        static::assertInstanceOf(Contact::class, $entry->getRecipients()->first());
        static::assertEquals($mail->getPlainBodyText(), $entry->getContentText());
    }
}
