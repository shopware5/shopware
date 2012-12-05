<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 5217
 */
class Shopware_RegressionTests_Ticket5217 extends Enlight_Components_Test_TestCase
{        
    /**
     * Test case method
     */
	public function testMailTransport()
	{
		$mailTransport = Shopware()->MailTransport();
		
		$mail = new Enlight_Components_Mail();
		
		$mail->setBodyText('Test Hallo');
		$mail->addTo('test@shopware.de');
		
		$mail->send($mailTransport);
	}
}