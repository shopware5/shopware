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

namespace Shopware\Tests\Functional\Components;

use Doctrine\ORM\EntityRepository;
use Enlight_Components_Mail;
use Enlight_Components_Test_TestCase;
use Enlight_Event_EventArgs;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Mail\Attachment;
use Shopware\Models\Mail\Mail;
use Shopware\Models\Shop\Shop;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware_Components_StringCompiler;
use Shopware_Components_TemplateMail;

class TemplateMailTest extends Enlight_Components_Test_TestCase
{
    use ContainerTrait;

    private Shopware_Components_TemplateMail $mail;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $stringCompiler = new Shopware_Components_StringCompiler($this->getContainer()->get('template'));

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findOneBy')->willReturn(null);

        $manager = $this->createMock(ModelManager::class);
        $manager->method('getRepository')->willReturn($repository);

        $this->mail = new Shopware_Components_TemplateMail();
        $this->mail->setShop($this->getContainer()->get('shop'));
        $this->mail->setModelManager($manager);
        $this->mail->setStringCompiler($stringCompiler);
    }

    public function testLoadValuesLoadsValues(): void
    {
        $mail = new Enlight_Components_Mail('UTF-8');
        $templateMock = $this->getSimpleMailMockObject();

        $result = $this->mail->loadValues($mail, $templateMock);

        static::assertInstanceOf(Enlight_Components_Mail::class, $result);
        static::assertEquals('UTF-8', $result->getCharset());
    }

    public function testLoadTemplateLoadsValues(): void
    {
        $mail = new Enlight_Components_Mail('UTF-8');
        $templateMock = $this->getSimpleMailMockObject();

        $result = $this->mail->loadValues($mail, $templateMock);

        static::assertEquals($templateMock->getSubject(), $result->getSubject());
        static::assertEquals($templateMock->getFromName(), $result->getFromName());
        static::assertEquals($templateMock->getFromMail(), $result->getFrom());
        static::assertEquals($templateMock->getContent(), $result->getBodyText(true));
        static::assertEquals($templateMock->getContentHtml(), $result->getBodyHtml(true));
    }

    /**
     * @depends testLoadTemplateLoadsValues
     */
    public function testLoadSmartyTemplateLoadsValues(): void
    {
        $mail = new Enlight_Components_Mail('UTF-8');
        $templateMock = $this->getSmartyMailMockObject();

        $context = [
            'sConfig' => ['sSHOPNAME' => 'Shopware 5 Demo', 'sMAIL' => 'info@example.com'],
            'sShopURL' => 'http://demo.shopware.de',
        ];

        $this->mail->getStringCompiler()->setContext($context);

        $result = $this->mail->loadValues($mail, $templateMock);

        static::assertEquals('Ihr Bestellung bei Shopware 5 Demo', $result->getSubject());
        static::assertEquals('Shopware 5 Demo', $result->getFromName());
        static::assertEquals('info@example.com', $result->getFrom());
        static::assertEquals('Testbestellung bei Shopware 5 Demo', $result->getBodyText(true));
        static::assertEquals('Testbestellung HTML bei Shopware 5 Demo', $result->getBodyHtml(true));
    }

    public function testCreateMailWorks(): void
    {
        $templateMock = $this->getSmartyMailMockObject();

        $context = [
            'sConfig' => ['sSHOPNAME' => 'Shopware 5 Demo', 'sMAIL' => 'info@example.com'],
            'sShopURL' => 'http://demo.shopware.de',
        ];

        $result = $this->mail->createMail($templateMock, $context);

        static::assertEquals('Ihr Bestellung bei Shopware 5 Demo', $result->getSubject());
        static::assertEquals('Shopware 5 Demo', $result->getFromName());
        static::assertEquals('info@example.com', $result->getFrom());
        static::assertEquals('Testbestellung bei Shopware 5 Demo', $result->getBodyText(true));
        static::assertEquals('Testbestellung HTML bei Shopware 5 Demo', $result->getBodyHtml(true));
    }

    public function testCreateMailEventMailContextIsConsidered(): void
    {
        $eventManager = $this->getContainer()->get('events');
        $eventManager->addListener('TemplateMail_CreateMail_MailContext', function (Enlight_Event_EventArgs $args) {
            $context = $args->getReturn();
            $context['sConfig']['sSHOPNAME'] = 'Shopware Foo Bar Demo';

            return $context;
        });

        $templateMock = $this->getSmartyMailMockObject();

        $context = [
            'sConfig' => ['sSHOPNAME' => 'Shopware 5 Demo', 'sMAIL' => 'info@example.com'],
            'sShopURL' => 'https://shopware.local',
        ];

        $result = $this->mail->createMail($templateMock, $context);

        static::assertEquals('Ihr Bestellung bei Shopware Foo Bar Demo', $result->getSubject());
        static::assertEquals('Shopware Foo Bar Demo', $result->getFromName());
        static::assertEquals('info@example.com', $result->getFrom());
        static::assertEquals('Testbestellung bei Shopware Foo Bar Demo', $result->getBodyText(true));
        static::assertEquals('Testbestellung HTML bei Shopware Foo Bar Demo', $result->getBodyHtml(true));

        $listeners = $eventManager->getListeners('TemplateMail_CreateMail_MailContext');
        foreach ($listeners as $listener) {
            $eventManager->removeListener($listener);
        }
    }

    public function testCreateMailWithInvalidTemplateNameShouldThrowException(): void
    {
        $this->expectException('Enlight_Exception');
        $this->mail->createMail('ThisIsNoTemplateName', []);
    }

    /**
     * Sending mails through cron without having shop set
     */
    public function testCreateMailWithoutShop(): void
    {
        $templateMail = new Shopware_Components_TemplateMail();
        $templateMail->setModelManager($this->getContainer()->get(ModelManager::class));
        $templateMail->setStringCompiler(new Shopware_Components_StringCompiler($this->getContainer()->get('template')));

        $mail = $templateMail->createMail('sOrder');

        static::assertInstanceOf(Enlight_Components_Mail::class, $mail);
    }

    /**
     * Tests the mail creation if the passed shop does not have a template, but its main shop does.
     */
    public function testCreateMailWithoutShopTemplate(): void
    {
        // Prepare new shop without template
        $entityManager = $this->getContainer()->get(ModelManager::class);
        $defaultShop = $entityManager->find(Shop::class, 1);
        $newShop = new Shop();
        $newShop->setMain($defaultShop);
        $newShop->setName('New Shop');
        $entityManager->persist($newShop);
        $entityManager->flush($newShop);

        // Test mail creation
        $registerConfirmationMail = $entityManager->find(Mail::class, 1);
        static::assertInstanceOf(Mail::class, $registerConfirmationMail);
        $mail = $this->mail->createMail($registerConfirmationMail, [], $newShop);
        static::assertInstanceOf(Enlight_Components_Mail::class, $mail);

        // Revert changes in the database
        $entityManager->remove($newShop);
        $entityManager->flush($newShop);
    }

    /**
     * Tests the mail creation if the passed shop and its main shop does not have templates.
     */
    public function testCreateMailWithoutMainShopTemplate(): void
    {
        // Prepare new shop and main shop without templates
        $entityManager = $this->getContainer()->get(ModelManager::class);
        $newMainShop = new Shop();
        $newMainShop->setName('New Main Shop');
        $entityManager->persist($newMainShop);
        $newShop = new Shop();
        $newShop->setMain($newMainShop);
        $newShop->setName('New Shop');
        $entityManager->persist($newShop);
        $entityManager->flush([$newMainShop, $newShop]);

        // Test mail creation
        $registerConfirmationMail = $entityManager->find(Mail::class, 1);
        static::assertInstanceOf(Mail::class, $registerConfirmationMail);
        $mail = $this->mail->createMail($registerConfirmationMail, [], $newShop);
        static::assertInstanceOf(Enlight_Components_Mail::class, $mail);

        // Revert changes in the database
        $entityManager->remove($newMainShop);
        $entityManager->remove($newShop);
        $entityManager->flush([$newMainShop, $newShop]);
    }

    private function getAttachmentMockObject(): Attachment
    {
        $attachmentMock = $this->createMock(Attachment::class);

        $attachmentMock->method('getPath')
            ->willReturn(__FILE__);

        $attachmentMock->method('getName')
            ->willReturn('foobar.pdf');

        $attachmentMock->method('getFileName')
            ->willReturn('foobar.pdf')
            ->willReturn('foobar.pdf');

        return $attachmentMock;
    }

    private function getSimpleMailMockObject(): Mail
    {
        $templateMock = $this->createMock(Mail::class);

        $templateMock->method('getFromMail')
            ->willReturn('info@demo.shopware.de');

        $templateMock->method('getFromName')
            ->willReturn('Shopware 5 Demo');

        $templateMock->method('getSubject')
            ->willReturn('Shopware 5 Testmail');

        $templateMock->method('getContent')
            ->willReturn('Testcontent');

        $templateMock->method('getContentHtml')
            ->willReturn('Testcontent HTML');

        $templateMock->method('isHtml')
            ->willReturn(true);

        $templateMock->method('getAttachments')
            ->willReturn([$this->getAttachmentMockObject()]);

        return $templateMock;
    }

    private function getSmartyMailMockObject(): Mail
    {
        $mail = new Mail();
        $mail->setFromMail('{$sConfig.sMAIL}');
        $mail->setFromName('{$sConfig.sSHOPNAME}');
        $mail->setSubject('Ihr Bestellung bei {$sConfig.sSHOPNAME}');
        $mail->setContent('Testbestellung bei {$sConfig.sSHOPNAME}');
        $mail->setContentHtml('Testbestellung HTML bei {$sConfig.sSHOPNAME}');
        $mail->setIsHtml();

        return $mail;
    }
}
