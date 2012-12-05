<?php
/**
 * Test case
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @author Temporary
 * @package Shopware
 * @subpackage IntegrationTests
 */
class Shopware_DeploymentTests_SomethingTest extends Enlight_Components_Test_Selenium_TestCase
{
	/**
	 * Test suggest search
	 *
	 * @return void
	 */
	public function testSuggestSearch()
	{
		$this->open('search');
        $this->focus("id=searchfield");
        $this->type("id=searchfield", "melami");
        $this->click('searchfield');
		$this->waitForElementPresent('css=a.resultlink > h3');
		$this->clickAndWait('css=a.resultlink > h3');
		$this->verifyTextPresent('Melamin Schale');
	}
}