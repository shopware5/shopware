<?php
/**
 * Shopware
 *
 * LICENSE
 *
 * Available through the world-wide-web at this URL:
 * http://shopware.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Shopware
 * @package    Shopware_Tests
 * @subpackage TemplateMail
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 * @author     Benjamin Cremer
 * @author     $Author$
 */

/**
 * Test Class for Shopware_Components_TemplateMail
 *
 * @category   Shopware
 * @package    Shopware_Tests
 * @subpackage TemplateMail
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 */
class Shopware_Tests_Components_TemplateMailTest extends Enlight_Components_Test_TestCase
{
    /**
     * @var \Shopware_Components_TemplateMail $mail
     */
    private $mail;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $stringCompiler = new Shopware_Components_StringCompiler(Shopware()->Template());

        $manager = $this->getMockBuilder('Shopware\Components\Model\ModelManager')
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->any())
                ->method('getRepository')
                ->will($this->returnSelf());

        $manager->expects($this->any())
                ->method('findOneBy')
                ->will($this->returnValue(false));

        $this->mail = new Shopware_Components_TemplateMail();
        $this->mail->setShop(Shopware()->Shop());
        $this->mail->setModelManager($manager);
        $this->mail->setStringCompiler($stringCompiler);
    }

    /**
     * @return \Shopware\Models\Mail\Attachment
     */
    protected function getAttachmentMockObject()
    {
        $attachmentMock = $this->getMockBuilder('\Shopware\Models\Mail\Attachment')
                               ->disableOriginalConstructor()
                               ->getMock();

        $attachmentMock->expects($this->any())
                       ->method('getPath')
                       ->will($this->returnValue(__FILE__));

        $attachmentMock->expects($this->any())
                       ->method('getName')
                       ->will($this->returnValue('foobar.pdf'));

        $attachmentMock->expects($this->any())
                       ->method('getFileName')
                       ->will($this->returnValue('foobar.pdf'));

        return $attachmentMock;
    }

    /**
     * @return \Shopware\Models\Mail\Mail
     */
    protected function getSimpleMailMockObject()
    {
        $templateMock = $this->getMockBuilder('\Shopware\Models\Mail\Mail')
                             ->disableOriginalConstructor()
                             ->getMock();

        $templateMock->expects($this->any())
                     ->method('getFromMail')
                     ->will($this->returnValue('info@demo.shopware.de'));

        $templateMock->expects($this->any())
                     ->method('getFromName')
                     ->will($this->returnValue('Shopware 4 Demo'));

        $templateMock->expects($this->any())
                     ->method('getSubject')
                     ->will($this->returnValue('Shopware 4 Testmail'));

        $templateMock->expects($this->any())
                     ->method('getContent')
                     ->will($this->returnValue('Testcontent'));

        $templateMock->expects($this->any())
                     ->method('getContentHtml')
                     ->will($this->returnValue('Testcontent HTML'));

        $templateMock->expects($this->any())
                     ->method('isHtml')
                     ->will($this->returnValue(true));

        $templateMock->expects($this->any())
                     ->method('getAttachments')
                     ->will($this->returnValue(array($this->getAttachmentMockObject())));

        return $templateMock;
    }

    /**
     * @return \Shopware\Models\Mail\Mail
     */
    protected function getSmartyMailMockObject()
    {
        $templateMock = $this->getMockBuilder('\Shopware\Models\Mail\Mail')
                             ->disableOriginalConstructor()
                             ->getMock();

        $templateMock->expects($this->any())
                     ->method('getFromMail')
                     ->will($this->returnValue('{$sConfig.sMAIL}'));

        $templateMock->expects($this->any())
                     ->method('getFromName')
                     ->will($this->returnValue('{$sConfig.sSHOPNAME}'));

        $templateMock->expects($this->any())
                     ->method('getSubject')
                     ->will($this->returnValue('Ihr Bestellung bei {$sConfig.sSHOPNAME}'));

        $templateMock->expects($this->any())
                     ->method('getContent')
                     ->will($this->returnValue('Testbestellung bei {$sConfig.sSHOPNAME}'));

        $templateMock->expects($this->any())
                     ->method('getContentHtml')
                     ->will($this->returnValue('Testbestellung HTML bei {$sConfig.sSHOPNAME}'));

        $templateMock->expects($this->any())
                     ->method('isHtml')
                     ->will($this->returnValue(true));

        return $templateMock;
    }

    /**
     * Test case
     */
    public function testShouldBeInstanceOfShopwareComponentsTemplateMail()
    {
        $this->assertInstanceOf('\Shopware_Components_TemplateMail', $this->mail);

        //$this->assertInstanceOf('\Shopware_Models_Shop', $this->mail->getShop());
        $this->assertInstanceOf('\Shopware_Components_StringCompiler', $this->mail->getStringCompiler());
        $this->assertInstanceOf('\Shopware\Components\Model\ModelManager', $this->mail->getModelManager());
    }

    /**
     * Test case
     */
    public function testLoadValuesLoadsValues()
    {
        $mail = new Enlight_Components_Mail('UTF-8');
        $templateMock = $this->getSimpleMailMockObject();

        $result = $this->mail->loadValues($mail, $templateMock);

        $this->assertInstanceOf('\Enlight_Components_Mail', $result);
        $this->assertEquals('UTF-8', $result->getCharset());
    }

    /**
     * Test case
     */
    public function testLoadTemplateLoadsValues()
    {
        $mail = new Enlight_Components_Mail('UTF-8');
        $templateMock = $this->getSimpleMailMockObject();

        $result = $this->mail->loadValues($mail, $templateMock);

        $this->assertEquals($templateMock->getSubject(),     $result->getSubject());
        $this->assertEquals($templateMock->getFromName(),    $result->getFromName());
        $this->assertEquals($templateMock->getFromMail(),    $result->getFrom());
        $this->assertEquals($templateMock->getContent(),     $result->getBodyText(true));
        $this->assertEquals($templateMock->getContentHtml(), $result->getBodyHtml(true));
    }

    /**
     * Test case
     *
     * @depends testLoadTemplateLoadsValues
     */
    public function testLoadSmartyTemplateLoadsValues()
    {
        $mail = new Enlight_Components_Mail('UTF-8');
        $templateMock = $this->getSmartyMailMockObject();

        $context = array(
            'sConfig'   => array('sSHOPNAME' => 'Shopware 3.5 Demo', 'sMAIL' => 'info@example.com'),
            'sShopURL'  => 'http://demo.shopware.de',
        );

        $this->mail->getStringCompiler()->setContext($context);

        $result = $this->mail->loadValues($mail, $templateMock);

        $this->assertEquals('Ihr Bestellung bei Shopware 3.5 Demo',      $result->getSubject());
        $this->assertEquals('Shopware 3.5 Demo',                         $result->getFromName());
        $this->assertEquals('info@example.com',                          $result->getFrom());
        $this->assertEquals('Testbestellung bei Shopware 3.5 Demo',      $result->getBodyText(true));
        $this->assertEquals('Testbestellung HTML bei Shopware 3.5 Demo', $result->getBodyHtml(true));
    }

    /**
     * Test case
     * todo@bc implement some kind of testmode for templatemailer
     */
    public function testCreateMailWorks()
    {
        $templateMock = $this->getSmartyMailMockObject();

        $context = array(
            'sConfig'   => array('sSHOPNAME' => 'Shopware 3.5 Demo', 'sMAIL' => 'info@example.com'),
            'sShopURL'  => 'http://demo.shopware.de',
        );

        $result = $this->mail->createMail($templateMock, $context);

        $this->assertEquals('Ihr Bestellung bei Shopware 3.5 Demo',      $result->getSubject());
        $this->assertEquals('Shopware 3.5 Demo',                         $result->getFromName());
        $this->assertEquals('info@example.com',                          $result->getFrom());
        $this->assertEquals('Testbestellung bei Shopware 3.5 Demo',      $result->getBodyText(true));
        $this->assertEquals('Testbestellung HTML bei Shopware 3.5 Demo', $result->getBodyHtml(true));
    }

    /**
     * Test case
     *
     * @expectedException \Enlight_Exception
     */
    public function testCreateMailWithInvalidTemplateNameShouldThrowException()
    {
        $this->mail->createMail('ThisIsNoTemplateName', array());
    }
}
