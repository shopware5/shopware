<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_RegressionTests_Ticket5226 extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * adds and writes a test file
     */
    public function setUp()
    {
        parent::setUp();

        $loremIpsum = "Lorem ipsum dolor sit amet, consetetur sadipscing elitr,
        sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.
        At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus
        est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
        eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam
        et justo duo dolores et ea rebum. Stet clita kasd gubergren,
        no sea takimata sanctus est Lorem ipsum dolor sit amet.";

        $filePath = Shopware()->OldPath() . 'files/'.Shopware()->Config()->get('sESDKEY');
        mkdir($filePath, 0777);
        file_put_contents($filePath . '/shopware_packshot_community_edition_72dpi_rgb.png', $loremIpsum);
    }

    /**
     * Test if the download goes threw php
     */
    public function testDownloadESDViaPhp()
    {
        $this->Request()
            ->setMethod('POST')
            ->setPost('email', 'test@example.com')
            ->setPost('password', 'shopware');

        $this->dispatch('/account/login');
        $this->reset();

        $params["esdID"] = 204;
        $this->Request()->setParams($params);
        $this->dispatch('/account/download');

        $header = $this->Response()->getHeaders();
        $this->assertEquals("Content-Disposition", $header[1]["name"]);
        $this->assertEquals('attachment; filename="shopware_packshot_community_edition_72dpi_rgb.png"', $header[1]["value"]);
        $this->assertEquals('Content-Length', $header[2]["name"]);
        $this->assertGreaterThan(630, intval($header[2]["value"]));
        $this->assertEquals(strlen($this->Response()->getBody()), intval($header[2]["value"]));
    }

}
