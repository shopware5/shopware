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
 * @author     J.Schwehn
 * @author     $Author$
 */

/**
 * Test case
 *
 * @category   Shopware
 * @package    Shopware_Tests
 * @subpackage Controllers
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @group Banner
 * @group Shopware_Tests
 * @group Controllers
 */
class Shopware_Tests_Controllers_Backend_BannerTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Supplier dummy data
     * 
     * @var array
     */
    private $bannerData = array(
        'description' => '__bannerTest',
        'link' => 'http://www.example.com',
        'active_from_date' => '2012-03-28T00:00:00', 
        'active_from_time' => '2012-03-28T00:00:01', 
        'active_to_date' => '2020-03-28T00:00:00', 
        'active_to_time' => '2020-03-28T23:59:59', 
        'image' => 'media/image/testImage.jpg' ,
        'media-manager-selection' => 'media/image/testImage.jpg',
        'categoryId' => 3
    );
    private $lastBannerId = null;
    private $categoryId = 3;
    
    
    /**
     * Standard set up for every test - just disable auth 
     */
    public function setUp() 
    {
        // disable auth
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }
    
    public function testGetBannerForCategory()
    {
        $this->Request()->setMethod('POST')->setPost(array('filter' => '3'));
        /** @var Enlight_Controller_Response_ResponseTestCase */
        $this->dispatch('backend/banner/getAllBanners?&categoryId=3&page=1&start=0&limit=30');
        $this->assertTrue($this->View()->success);
    }
    
    /**
     * @return mixed
     */
    public function testAddBannerToCategory()
    {
        $this->Request()->setMethod('POST')->setPost($this->bannerData);
        $response = $this->dispatch('backend/banner/createBanner');
        $this->assertTrue((boolean)$this->View()->success);
        $jsonBody = $this->View()->getAssign();
        $this->assertEquals('true',$jsonBody['success']);
        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
        return $jsonBody['data'];
    }
    /**
     * @return mixed
     * @depends testAddBannerToCategory
     * @param $lastBanner array
     */
    public function testUpdateBannerToCategory($lastBanner)
    {
        foreach($lastBanner as $key=>$value) {
            if(!is_null($value)) {
                $banner[$key] = $value;
            }
        }
        $banner['description'] = "___testBanner_UPDATE";
        $this->Request()->setMethod('POST')->setPost($banner);
        $response = $this->dispatch('backend/banner/updateBanner');
        $this->assertTrue((boolean)$this->View()->success);
        $jsonBody = $this->View()->getAssign();
        $this->assertEquals('true',$jsonBody['success']);
        $this->assertArrayHasKey('data', $jsonBody);
        $this->assertArrayHasKey('success', $jsonBody);
        return $jsonBody['data'];
    }
    
     /**
     * Tests if the banner can be removed from the database
     * The lastBanner is array based on the newly created one
     * 
     * @depends testUpdateBannerToCategory
     * @param $lastBanner array
     */
    public function testDeleteBannerFromCategory(array $lastBanner)
    {         
        $this->Request()->clearParams();
        foreach($lastBanner as $key=>$value) {
            if(!is_null($value)) {
                $this->Request()->setMethod('POST')->setPost( array($key => $value));
            }
        }
        $response = $this->dispatch('backend/banner/deleteBanner');
        $this->assertTrue($this->View()->success);
    }
}