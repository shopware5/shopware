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
 * @subpackage StringCompiler
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 * @author     Benjamin Cremer
 * @author     $Author$
 */

/**
 * Test Class for Shopware_Components_StringCompiler
 *
 * @category   Shopware
 * @package    Shopware_Tests
 * @subpackage StringCompiler
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 */
class Shopware_Tests_Components_StringCompilerTest extends Enlight_Components_Test_TestCase
{
    /**
     * @var \Shopware_Components_StringCompiler $compiler
     */
    private $compiler;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->compiler = new Shopware_Components_StringCompiler(Shopware()->Template());
    }

    /**
     * Test case
     */
    public function testShouldCompileCompatibilityMode()
    {
        $template = <<<'EOD'
Hallo,

ihre Zugangsdaten zu {sShopURL} lauten wie folgt:
Benutzer: {sMail}
Passwort: {sPassword}
EOD;

        $expectedResult = <<<'EOD'
Hallo,

ihre Zugangsdaten zu http://demo.shopware.de lauten wie folgt:
Benutzer: info@shopware.de
Passwort: 123muster
EOD;

        $context = array(
            'sShopURL'  => 'http://demo.shopware.de',
            'sMail'     => 'info@shopware.de',
            'sPassword' => '123muster',
        );

        $result = $this->compiler->compileCompatibilityMode($template, $context);

        $this->assertEquals($result, $expectedResult);
    }

    /**
     * Test case
     */
    public function testShouldCompileSmarty()
    {

        $template = <<<'EOD'
Email von {$sConfig.sSHOPNAME} ({$sConfig.sMAIL}) Hallo,
folgende Artikel haben den Mindestbestand unterschritten:
Bestellnummer Artikelname Bestand/Mindestbestand
{foreach from=$sJob.articles item=sArticle key=key}
{$sArticle.ordernumber} {$sArticle.name} {$sArticle.instock}/{$sArticle.stockmin}
{/foreach}
EOD;

        $expectedResult = <<<'EOD'
Email von Shopware 3.5 Demo (info@example.com) Hallo,
folgende Artikel haben den Mindestbestand unterschritten:
Bestellnummer Artikelname Bestand/Mindestbestand
111 test111 234/255
123 test123 111/123

EOD;

        $sJob['articles'] = array(
            array(
                'ordernumber' => '111',
                'name'        => 'test111',
                'instock'     => '234',
                'stockmin'    => '255',
            ),
            array(
                'ordernumber' => '123',
                'name'        => 'test123',
                'instock'     => '111',
                'stockmin'    => '123',
            ),
        );

        $context = array(
            'sConfig' => array('sSHOPNAME' => 'Shopware 3.5 Demo', 'sMAIL' => 'info@example.com'),
            'sJob'    => $sJob,
        );

        $result = $this->compiler->compileSmartyString($template, $context);

        $this->assertEquals($result, $expectedResult);
    }

    /**
     * Test case
     *
     * @depends testShouldCompileSmarty
     * @depends testShouldCompileCompatibilityMode
     */
    public function testShouldCompileMixedString()
    {

        $template = <<<'EOD'
Email von {$sConfig.sSHOPNAME} ({$sConfig.sMAIL})
Hallo,
ihre Zugangsdaten zu {sShopURL} lauten wie folgt:
Benutzer: {sMail}
Passwort: {sPassword}
EOD;

        $expectedResult = <<<'EOD'
Email von Shopware 3.5 Demo (info@example.com)
Hallo,
ihre Zugangsdaten zu http://demo.shopware.de lauten wie folgt:
Benutzer: info@shopware.de
Passwort: 123muster
EOD;
        $context = array(
            'sConfig'   => array('sSHOPNAME' => 'Shopware 3.5 Demo', 'sMAIL' => 'info@example.com'),
            'sShopURL'  => 'http://demo.shopware.de',
            'sMail'     => 'info@shopware.de',
            'sPassword' => '123muster',
        );

        $result = $this->compiler->compileString($template, $context);

        $this->assertEquals($result, $expectedResult);
    }

    /**
     * Test case
     *
     * @expectedException Enlight_Exception
     * @expectedExceptionMessage Syntax Error 74&quot;  on line 1 &quot;Hallo {$user|invalidmodifier}&quot; unknown modifier &quot;invalidmodifier&quot
     */
    public function testInvalidSmartyShouldThrowExceptionAndCustomExceptionMessage()
    {
        $defectSmartyString = 'Hallo {$user|invalidmodifier}';
        $this->compiler->compileString($defectSmartyString);
    }
}
