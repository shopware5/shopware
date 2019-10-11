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

namespace Shopware\Tests\Components;

class TemplateMailTest extends \Enlight_Components_Test_TestCase
{
    /**
     * @var \Shopware_Components_TemplateMail
     */
    private $mail;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $stringCompiler = new \Shopware_Components_StringCompiler(Shopware()->Template());

        $repository = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $repository->expects(static::any())
            ->method('findOneBy')
            ->willReturn(null);

        $manager = $this->createMock(\Shopware\Components\Model\ModelManager::class);
        $manager->expects(static::any())
                ->method('getRepository')
                ->willReturn($repository);

        $this->mail = new \Shopware_Components_TemplateMail();
        $this->mail->setShop(Shopware()->Shop());
        $this->mail->setModelManager($manager);
        $this->mail->setStringCompiler($stringCompiler);
    }

    /**
     * Test case
     */
    public function testShouldBeInstanceOfShopwareComponentsTemplateMail()
    {
        static::assertInstanceOf('\Shopware_Components_TemplateMail', $this->mail);

        static::assertInstanceOf('\Shopware_Components_StringCompiler', $this->mail->getStringCompiler());
        static::assertInstanceOf('\Shopware\Components\Model\ModelManager', $this->mail->getModelManager());
    }

    /**
     * Test case
     */
    public function testLoadValuesLoadsValues()
    {
        $mail = new \Enlight_Components_Mail('UTF-8');
        $templateMock = $this->getSimpleMailMockObject();

        $result = $this->mail->loadValues($mail, $templateMock);

        static::assertInstanceOf('\Enlight_Components_Mail', $result);
        static::assertEquals('UTF-8', $result->getCharset());
    }

    /**
     * Test case
     */
    public function testLoadTemplateLoadsValues()
    {
        $mail = new \Enlight_Components_Mail('UTF-8');
        $templateMock = $this->getSimpleMailMockObject();

        $result = $this->mail->loadValues($mail, $templateMock);

        static::assertEquals($templateMock->getSubject(), $result->getSubject());
        static::assertEquals($templateMock->getFromName(), $result->getFromName());
        static::assertEquals($templateMock->getFromMail(), $result->getFrom());
        static::assertEquals($templateMock->getContent(), $result->getBodyText(true));
        static::assertEquals($templateMock->getContentHtml(), $result->getBodyHtml(true));
    }

    /**
     * Test case
     *
     * @depends testLoadTemplateLoadsValues
     */
    public function testLoadSmartyTemplateLoadsValues()
    {
        $mail = new \Enlight_Components_Mail('UTF-8');
        $templateMock = $this->getSmartyMailMockObject();

        $context = [
            'sConfig' => ['sSHOPNAME' => 'Shopware 3.5 Demo', 'sMAIL' => 'info@example.com'],
            'sShopURL' => 'http://demo.shopware.de',
        ];

        $this->mail->getStringCompiler()->setContext($context);

        $result = $this->mail->loadValues($mail, $templateMock);

        static::assertEquals('Ihr Bestellung bei Shopware 3.5 Demo', $result->getSubject());
        static::assertEquals('Shopware 3.5 Demo', $result->getFromName());
        static::assertEquals('info@example.com', $result->getFrom());
        static::assertEquals('Testbestellung bei Shopware 3.5 Demo', $result->getBodyText(true));
        static::assertEquals('Testbestellung HTML bei Shopware 3.5 Demo', $result->getBodyHtml(true));
    }

    /**
     * Test case
     * todo@bc implement some kind of testmode for templatemailer
     */
    public function testCreateMailWorks()
    {
        $templateMock = $this->getSmartyMailMockObject();

        $context = [
            'sConfig' => ['sSHOPNAME' => 'Shopware 3.5 Demo', 'sMAIL' => 'info@example.com'],
            'sShopURL' => 'http://demo.shopware.de',
        ];

        $result = $this->mail->createMail($templateMock, $context);

        static::assertEquals('Ihr Bestellung bei Shopware 3.5 Demo', $result->getSubject());
        static::assertEquals('Shopware 3.5 Demo', $result->getFromName());
        static::assertEquals('info@example.com', $result->getFrom());
        static::assertEquals('Testbestellung bei Shopware 3.5 Demo', $result->getBodyText(true));
        static::assertEquals('Testbestellung HTML bei Shopware 3.5 Demo', $result->getBodyHtml(true));
    }

    /**
     * Test case
     */
    public function testCreateMailWithInvalidTemplateNameShouldThrowException()
    {
        $this->expectException('Enlight_Exception');
        $this->mail->createMail('ThisIsNoTemplateName', []);
    }

    /**
     * Sending mails throught cron without having shop set
     */
    public function testCreateMailWithoutShop()
    {
        $templateMail = new \Shopware_Components_TemplateMail();
        $templateMail->setModelManager(Shopware()->Models());
        $templateMail->setStringCompiler(new \Shopware_Components_StringCompiler(Shopware()->Template()));

        $mail = $templateMail->createMail('sOrder');

        static::assertInstanceOf(\Enlight_Components_Mail::class, $mail);
    }

    /**
     * Tests the mail creation if the passed shop does not have a template, but its main shop does.
     */
    public function testCreateMailWithoutShopTemplate()
    {
        // Prepare new shop without template
        $entityManager = Shopware()->Container()->get('models');
        $defaultShop = $entityManager->find(\Shopware\Models\Shop\Shop::class, 1);
        $newShop = new \Shopware\Models\Shop\Shop();
        $newShop->setMain($defaultShop);
        $newShop->setName('New Shop');
        $entityManager->persist($newShop);
        $entityManager->flush($newShop);

        // Test mail creation
        $registerConfirmationMail = $entityManager->find(\Shopware\Models\Mail\Mail::class, 1);
        $mail = $this->mail->createMail($registerConfirmationMail, [], $newShop);
        static::assertInstanceOf(\Enlight_Components_Mail::class, $mail);

        // Revert changes in the database
        $entityManager->remove($newShop);
        $entityManager->flush($newShop);
    }

    /**
     * Tests the mail creation if the passed shop and its main shop does not have templates.
     */
    public function testCreateMailWithoutMainShopTemplate()
    {
        // Prepare new shop and main shop without templates
        $entityManager = Shopware()->Container()->get('models');
        $newMainShop = new \Shopware\Models\Shop\Shop();
        $newMainShop->setName('New Main Shop');
        $entityManager->persist($newMainShop);
        $newShop = new \Shopware\Models\Shop\Shop();
        $newShop->setMain($newMainShop);
        $newShop->setName('New Shop');
        $entityManager->persist($newShop);
        $entityManager->flush([$newMainShop, $newShop]);

        // Test mail creation
        $registerConfirmationMail = $entityManager->find(\Shopware\Models\Mail\Mail::class, 1);
        $mail = $this->mail->createMail($registerConfirmationMail, [], $newShop);
        static::assertInstanceOf(\Enlight_Components_Mail::class, $mail);

        // Revert changes in the database
        $entityManager->remove($newMainShop);
        $entityManager->remove($newShop);
        $entityManager->flush([$newMainShop, $newShop]);
    }

    /**
     * @return \Shopware\Models\Mail\Attachment
     */
    protected function getAttachmentMockObject()
    {
        $attachmentMock = $this->createMock(\Shopware\Models\Mail\Attachment::class);

        $attachmentMock->expects(static::any())
                       ->method('getPath')
                       ->willReturn(__FILE__);

        $attachmentMock->expects(static::any())
                       ->method('getName')
                       ->willReturn('foobar.pdf');

        $attachmentMock->expects(static::any())
                       ->method('getFileName')
                       ->willReturn('foobar.pdf')
                       ->willReturn('foobar.pdf');

        return $attachmentMock;
    }

    /**
     * @return \Shopware\Models\Mail\Mail
     */
    protected function getSimpleMailMockObject()
    {
        $templateMock = $this->createMock(\Shopware\Models\Mail\Mail::class);

        $templateMock->expects(static::any())
                     ->method('getFromMail')
                     ->willReturn('info@demo.shopware.de');

        $templateMock->expects(static::any())
                     ->method('getFromName')
                     ->willReturn('Shopware 5 Demo');

        $templateMock->expects(static::any())
                     ->method('getSubject')
                     ->willReturn('Shopware 5 Testmail');

        $templateMock->expects(static::any())
                     ->method('getContent')
                     ->willReturn('Testcontent');

        $templateMock->expects(static::any())
                     ->method('getContentHtml')
                     ->willReturn('Testcontent HTML');

        $templateMock->expects(static::any())
                     ->method('isHtml')
                     ->willReturn(true);

        $templateMock->expects(static::any())
                     ->method('getAttachments')
                     ->willReturn([$this->getAttachmentMockObject()]);

        return $templateMock;
    }

    /**
     * @return \Shopware\Models\Mail\Mail
     */
    protected function getSmartyMailMockObject()
    {
        $templateMock = $this->createMock(\Shopware\Models\Mail\Mail::class);

        $templateMock->expects(static::any())
                     ->method('getFromMail')
                     ->willReturn('{$sConfig.sMAIL}');

        $templateMock->expects(static::any())
                     ->method('getFromName')
                     ->willReturn('{$sConfig.sSHOPNAME}');

        $templateMock->expects(static::any())
                     ->method('getSubject')
                     ->willReturn('Ihr Bestellung bei {$sConfig.sSHOPNAME}');

        $templateMock->expects(static::any())
                     ->method('getContent')
                     ->willReturn('Testbestellung bei {$sConfig.sSHOPNAME}');

        $templateMock->expects(static::any())
                     ->method('getContentHtml')
                     ->willReturn('Testbestellung HTML bei {$sConfig.sSHOPNAME}');

        $templateMock->expects(static::any())
                     ->method('isHtml')
                     ->willReturn(true);

        return $templateMock;
    }
}
