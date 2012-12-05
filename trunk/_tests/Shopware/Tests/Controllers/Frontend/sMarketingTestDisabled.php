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
 * Test case for sMarketing
 *
 * @category   Shopware
 * @package    Shopware_Tests
 * @subpackage Controllers
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @group Banner
 * @group Shopware_Tests
 * @group Controllers
 * @group Frontend
 * @group sMarketing
 */
class Shopware_Tests_Controllers_Frontend_BannerTest extends Enlight_Components_Test_Controller_TestCase
{
    private $testData = array(
        'id' => '14',
        'description' => 'Technik und Elektronik',
        'valid_from' => null,
        'valid_to' => null,
        'link' => '',
        'link_target' => '_parent',
        'categoryID' => '3',
        'extension' => 'jpg'
);
    public function testGetSingleBannerForCategory()
    {
        $categoryId  = 3;
        $limit = 1;
        $result = Shopware()->Modules()->Marketing()->sBanner($categoryId, $limit);
        
        $this->assertTrue(is_array($result));
        // make shure that every single key is present
        foreach($this->testData as $key=>$value) {
            $this->assertArrayHasKey($key, $result);
        }
    }
    public function testGetMultipleBannerForCategory()
    {
        $categoryId  = 3;
        $limit = 2;
        $result = Shopware()->Modules()->Marketing()->sBanner($categoryId, $limit);
        
        $this->assertTrue(is_array($result));
        $this->assertCount(2, $result);
        
        foreach($result as $banner) {
            // make shure that every single key is present
            foreach($this->testData as $key=>$value) {
                $this->assertArrayHasKey($key, $banner);
                
            }
            if(14 == $banner['id']) {
                foreach($this->testData as $bannerKey=>$bannerValue) {
                    $this->assertEquals($banner[$bannerKey], $bannerValue);
                }
            }
        }
    }
}