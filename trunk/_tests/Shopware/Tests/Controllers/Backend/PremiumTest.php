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
 * @author     Heiner Lohaus
 * @author     $Author$
 */

class Shopware_Tests_Controllers_Backend_PremiumTest extends Enlight_Components_Test_Controller_TestCase
{
    private $premiumData = array(
        'orderNumber'=>'SW2001_test',
        'pseudoOrderNumber'=>'SW123',
        'startPrice'=>123,
        'shopId'=>1
    );

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
    public function testGetPremiumArticles(){
        /** @var Enlight_Controller_Response_ResponseTestCase */
        $response = $this->dispatch('backend/premium/getPremiumArticles');
        $this->assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        $this->assertArrayHasKey('total', $jsonBody);
        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);

        //Testing the search-function
        $filter = array(
            'filter'=>Zend_Json::encode(array(array(
                'value'=>'test'
            )))
        );
        $this->Request()->setMethod('POST')->setPost($filter);
        $this->dispatch('backend/premium/getPremiumArticles');
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
    public function testCreatePremiumArticle(){

        $this->Request()->setMethod('POST')->setPost($this->premiumData);

        $response = $this->dispatch('backend/premium/createPremiumArticle');
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
     * @depends testCreatePremiumArticle
     */
    public function testEditPremiumArticle($lastId){
        $premiumData = $this->premiumData;
        $premiumData['pseudoOrderNumber'] = 'SW987';
        $premiumData['id'] = $lastId;

        $this->Request()->setMethod('POST')->setPost($premiumData);

        $response = $this->dispatch('backend/premium/editPremiumArticle');

        $jsonBody = $this->View()->getAssign();

        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
    }

    /**
     * This test-method tests the deleting of a premium-article.
     *
     * @depends testCreatePremiumArticle
     * @param $lastId
     */
    public function testDeletePremiumArticle($lastId){
        $this->Request()->setMethod('POST')->setPost(array('id'=>$lastId));

        $response = $this->dispatch('backend/premium/deletePremiumArticle');

        $jsonBody = $this->View()->getAssign();

        $this->assertArrayHasKey('success', $jsonBody);
    }
}