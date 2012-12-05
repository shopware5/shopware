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
 * @subpackage Controllers
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 * @author     Patrick Stahl
 * @author     $Author$
 */

class Shopware_Tests_Controllers_Backend_RiskManagementTest extends Enlight_Components_Test_Controller_TestCase
{

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp()
    {
        parent::setUp();

        // disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    /**
     * Tests the getPremiumArticlesAction()
     * to test if reading the articles is working
     * Additionally this method tests the search-function
     */
    public function testGetPayments(){
        /** @var Enlight_Controller_Response_ResponseTestCase */
        $response = $this->dispatch('backend/risk_management/getPayments');
        $this->assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        $this->assertArrayHasKey('total', $jsonBody);
        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
    }

    /**
     * This test tests the creating of a new premium-article.
     * The response has to contain the id of the created article.
     * This function is called before testEditPremiumArticle and testDeletePremiumArticle
     * @return mixed
     */
    public function testCreateRule()
	{

		$manager    = Shopware()->Models();
		/**
		 * @var $repository Shopware\Models\Payment\RuleSet
		 */
		$repository = Shopware()->Models()->getRepository('Shopware\Models\Payment\RuleSet');

		$rules = $repository->findBy(array('paymentId' => 2));
		foreach($rules as $rule){
			$manager->remove($rule);
		}

		$manager->flush();

        $this->Request()->setMethod('POST')->setPost(
			array(
				'paymentId' => 2,
				'rule1' 	=> 'CUSTOMERGROUPISNOT',
				'rule2' 	=> '',
				'value1'	=> '5',
				'value2'	=> ''
			)
		);

        $this->dispatch('backend/risk_management/createRule');
        $this->assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
        $this->assertArrayHasKey('id', $jsonBody['data']);

        return $jsonBody['data']['id'];
    }

    /**
     * This test method tests the editing of
     * a premium-article.
     * The testCreatePremiumArticle method is called before.
     *
     * @param $lastId The id of the last created article
     * @depends testCreateRule
     */
    public function testEditRule($lastId){
        $this->Request()->setMethod('POST')->setPost(
			array(
				'id' => $lastId,
				'paymentId' => 2,
				'rule1' 	=> 'CUSTOMERGROUPISNOT',
				'rule2' 	=> '',
				'value1'	=> '8',
				'value2'	=> ''
			)
		);

        $this->dispatch('backend/risk_management/editRule');

        $jsonBody = $this->View()->getAssign();

        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);

    }

    /**
     * This test-method tests the deleting of a premium-article.
     *
     * @depends testCreateRule
     * @param $lastId
     */
    public function testDeleteRule($lastId){
        $this->Request()->setMethod('POST')->setPost(array('id'=>$lastId));

        $response = $this->dispatch('backend/risk_management/deleteRule');

        $jsonBody = $this->View()->getAssign();

        $this->assertArrayHasKey('success', $jsonBody);
    }
}