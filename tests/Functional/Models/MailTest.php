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

namespace Shopware\Tests\Functional\Models;

use Enlight_Components_Test_TestCase;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Mail\Mail;
use Shopware\Models\Mail\Repository;
use Shopware\Models\Order\Status;

class MailTest extends Enlight_Components_Test_TestCase
{
    /**
     * @var array<string, mixed>
     */
    public array $testData = [
        'name' => 'Testmail123',
        'fromMail' => 'Shopware Demoshop',
        'fromName' => 'test@example.com',
        'subject' => 'Test Email Subject',
        'content' => 'Plaintext Content Example',
        'contentHtml' => 'HTML Context Example',
        'isHtml' => true,
        'mailtype' => 2,
        'context' => [
            'sShop' => 'Shopware',
            'sConfig' => [
                'lang' => [
                    'iso' => 'de',
                    'id' => 5,
                ],
                'sMail' => 'test@example.com',
            ],
        ],
    ];

    /**
     * @var array<string, string>
     */
    public array $translation = [
        'fromMail' => 'Shopware Demoshop EN',
        'subject' => 'Test Email Subject EN',
        'content' => 'Plaintext Content Example EN',
    ];

    protected ModelManager $em;

    protected Repository $repo;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->em = Shopware()->Models();
        $this->repo = Shopware()->Models()->getRepository(Mail::class);
    }

    /**
     * Tear down
     */
    protected function tearDown(): void
    {
        $mail = $this->repo->findOneBy(['name' => 'Testmail123']);

        if (!empty($mail)) {
            $this->em->remove($mail);
            $this->em->flush();
        }
        parent::tearDown();
    }

    /**
     * Testcase
     */
    public function testGetterAndSetter(): void
    {
        $mail = new Mail();

        foreach ($this->testData as $field => $value) {
            $setMethod = 'set' . ucfirst($field);

            if (substr($field, 0, 2) === 'is') {
                $getMethod = $field;
            } else {
                $getMethod = 'get' . ucfirst($field);
            }

            $mail->$setMethod($value);

            static::assertEquals($mail->$getMethod(), $value);
        }
    }

    /**
     * Testcase
     */
    public function testFromArrayWorks(): void
    {
        $mail = new Mail();
        $mail->fromArray($this->testData);

        foreach ($this->testData as $fieldname => $value) {
            if (substr($fieldname, 0, 2) === 'is') {
                $getMethod = $fieldname;
            } else {
                $getMethod = 'get' . ucfirst($fieldname);
            }

            static::assertEquals($mail->$getMethod(), $value);
        }
    }

    /**
     * Testcase
     *
     * @depends testFromArrayWorks
     */
    public function testTranslationWorks(): void
    {
        $mail = new Mail();
        $mail->fromArray($this->testData);
        $mail->setTranslation($this->translation);

        $testData = array_merge($this->testData, $this->translation);

        foreach ($testData as $fieldname => $value) {
            if (substr($fieldname, 0, 2) === 'is') {
                $getMethod = $fieldname;
            } else {
                $getMethod = 'get' . ucfirst($fieldname);
            }

            static::assertEquals($value, $mail->$getMethod());
        }
    }

    /**
     * Testcase
     */
    public function testMailShouldBePersisted(): void
    {
        $mail = new Mail();
        $mail->fromArray($this->testData);

        $this->em->persist($mail);
        $this->em->flush();

        $mailId = $mail->getId();

        // remove mail from entity manager
        $this->em->detach($mail);
        unset($mail);

        $mail = $this->repo->find($mailId);

        foreach ($this->testData as $fieldname => $value) {
            if (substr($fieldname, 0, 2) === 'is') {
                $getMethod = $fieldname;
            } else {
                $getMethod = 'get' . ucfirst($fieldname);
            }

            static::assertEquals($mail->$getMethod(), $value);
        }
    }

    /**
     * Testcase
     */
    public function testGetAttachmentShouldReturnEmptyArrayInitial(): void
    {
        $mail = new Mail();
        static::assertEquals($mail->getAttachments(), []);
    }

    /**
     * Testcase
     */
    public function testGetStateShouldReturnNullInitial(): void
    {
        $mail = new Mail();
        static::assertEquals($mail->getStatus(), null);
    }

    /**
     * Testcase
     */
    public function testMailtypeShouldBeUserInitial(): void
    {
        $mail = new Mail();

        static::assertEquals(Mail::MAILTYPE_USER, $mail->getMailtype());
    }

    /**
     * Test OrderState Mail
     */
    public function testSetStateShouldSetMailtypeToState(): void
    {
        $statusMock = $this->createMock(Status::class);

        $statusMock->expects(static::any())
                ->method('getGroup')
                ->willReturn(Status::GROUP_STATE);

        $mail = new Mail();
        $mail->setStatus($statusMock);

        static::assertEquals(Mail::MAILTYPE_STATE, $mail->getMailtype());
    }

    /**
     * Test User Mail
     */
    public function testShouldReturnCorrectMailTypeIfTypeIsUser(): void
    {
        $mail = new Mail();
        $mail->setMailtype(Mail::MAILTYPE_USER);

        static::assertTrue($mail->isUserMail());
        static::assertFalse($mail->isSystemMail());
        static::assertFalse($mail->isOrderStateMail());
        static::assertFalse($mail->isPaymentStateMail());
    }

    /**
     * Test System Mail
     */
    public function testShouldReturnCorrectMailTypeIfTypeIsSystem(): void
    {
        $mail = new Mail();
        $mail->setMailtype(Mail::MAILTYPE_SYSTEM);

        static::assertFalse($mail->isUserMail());
        static::assertTrue($mail->isSystemMail());
        static::assertFalse($mail->isOrderStateMail());
        static::assertFalse($mail->isPaymentStateMail());
    }

    /**
     * Test OrderState Mail
     */
    public function testShouldReturnCorrectMailTypeIfTypeIsOrderState(): void
    {
        $statusMock = $this->createMock(Status::class);

        $statusMock->expects(static::any())
                   ->method('getGroup')
                   ->willReturn(Status::GROUP_STATE);

        $mail = new Mail();
        $mail->setMailtype(Mail::MAILTYPE_STATE);
        $mail->setStatus($statusMock);

        static::assertFalse($mail->isUserMail());
        static::assertFalse($mail->isSystemMail());
        static::assertTrue($mail->isOrderStateMail());
        static::assertFalse($mail->isPaymentStateMail());
    }

    /**
     * Test PaymentState Mail
     */
    public function testShouldReturnCorrectMailTypeIfTypeIsPaymentState(): void
    {
        $statusMock = $this->createMock(Status::class);

        $statusMock->expects(static::any())
                ->method('getGroup')
                ->willReturn(Status::GROUP_PAYMENT);

        $mail = new Mail();
        $mail->setMailtype(Mail::MAILTYPE_STATE);
        $mail->setStatus($statusMock);

        static::assertFalse($mail->isUserMail());
        static::assertFalse($mail->isSystemMail());
        static::assertFalse($mail->isOrderStateMail());
        static::assertTrue($mail->isPaymentStateMail());
    }

    /**
     * @covers \Shopware\Models\Mail\Mail::arrayGetPath
     */
    public function testArrayGetPath(): void
    {
        $input = [
            'sShop' => 'Shopware',
            'sConfig' => [
                'lang' => [
                    'iso' => 'de',
                    'id' => 5,
                ],
                'sMail' => 'test@example.com',
            ],
        ];

        $exceptedOutput = [
            'sShop' => 'Shopware',
            'sConfig.lang.iso' => 'de',
            'sConfig.lang.id' => 5,
            'sConfig.sMail' => 'test@example.com',
        ];

        $mail = new Mail();

        static::assertEquals($mail->arrayGetPath($input), $exceptedOutput);
    }
}
