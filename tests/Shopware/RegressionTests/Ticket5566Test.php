<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_RegressionTests_Ticket5566 extends Enlight_Components_Test_Controller_TestCase
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
     * Checks password confirmation field
     */
    public function testArticleXMLExport()
    {
	    @ini_set('memory_limit', '768M');
	    $this->Front()->setParam('noViewRenderer', false);

        $this->dispatch('/backend/ImportExport/exportArticles?format=xml&exportVariants=1');
	    $header = $this->Response()->getHeaders();

	    $this->assertEquals("Content-Disposition",$header[1]["name"]);
	    $this->assertEquals("Content-Transfer-Encoding",$header[2]["name"]);
	    $this->assertEquals("binary",$header[2]["value"]);
	    $xmlOutput = $this->Response()->getBody();

	    $xml = simplexml_load_string($xmlOutput, 'SimpleXMLElement', LIBXML_NOCDATA);
	    $results = $xml->articles;
	    $articleData = $this->simpleXml2array($results);

	    //check if the output data is correct
	    foreach ($articleData["article"] as $article) {
		    //check the main variant attribute data
		    $mainDetailData = $article["mainDetail"];
		    $attributeData = $mainDetailData["attribute"];
		    if(!empty($attributeData)) {
		        $this->assertNotEmpty($attributeData["id"]);
		        $this->assertNotEmpty($attributeData["articleId"]);
		        $this->assertNotEmpty($attributeData["articleDetailId"]);
		        $this->assertEquals($article["mainDetailId"],$attributeData["articleDetailId"]);
		    }

		    //check the variant attribute data
		    if(!empty($article["variants"])) {
			    foreach($article["variants"]["variant"] as $key => $variant) {
				    if(!is_int($key)) {
					    $variant = $article["variants"]["variant"];
				    }
				    if(!empty($variant)) {
					    $this->assertNotEmpty($variant["articleId"]);
					    $variantAttributeData = $variant["attribute"];
					    if(!empty($variantAttributeData)) {
						    $variantArticleId = $variant["articleId"];
						    $this->assertNotEmpty($variantAttributeData["id"]);
						    $this->assertNotEmpty($variantAttributeData["articleId"]);
						    $this->assertNotEmpty($variantAttributeData["articleDetailId"]);
						    $this->assertEquals($variantArticleId, $variantAttributeData["articleId"]);
						    $this->assertEquals($variant["id"], $variantAttributeData["articleDetailId"]);
					    }
					    //check if the variant prices are set
					    $this->assertNotEmpty($variant["prices"]);
					    $this->assertNotEmpty($variant["prices"]["price"]);
				    }
			    }
		    }
		    //check if the main prices are set
		    $this->assertNotEmpty($mainDetailData["prices"]);
		    $this->assertNotEmpty($mainDetailData["prices"]["price"]);
	    }
    }


	/**
	 * helper method to convert the object to an array
	 *
	 * @param SimpleXMLElement
	 * @return array|string
	 */
	public function simpleXml2array($xml)
	{
		if (get_class($xml) == 'SimpleXMLElement') {
			$attributes = $xml->attributes();
			foreach ($attributes as $k=>$v) {
				if ($v) $a[$k] = (string) $v;
			}
			$x = $xml;
			$xml = get_object_vars($xml);
		}
		if (is_array($xml)) {
			if (count($xml) == 0) return (string) $x; // for CDATA
			foreach ($xml as $key=>$value) {
				$r[$key] = $this->simplexml2array($value);
			}
			if (isset($a)) $r['@attributes'] = $a;    // Attributes
			return $r;
		}
		return (string) $xml;
	}
}
