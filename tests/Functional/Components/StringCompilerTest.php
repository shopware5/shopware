<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Components;

use Enlight_Components_Test_TestCase;
use Shopware_Components_StringCompiler;

class StringCompilerTest extends Enlight_Components_Test_TestCase
{
    private Shopware_Components_StringCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler = new Shopware_Components_StringCompiler(Shopware()->Template());
    }

    public function testShouldCompileCompatibilityMode(): void
    {
        $template = <<<'EOD'
Hallo,

ihre Zugangsdaten zu {sShopURL} lauten wie folgt:
Benutzer: {sMail}
Passwort: {sPassword}
EOD;

        $expectedResult = <<<'EOD'
Hallo,

ihre Zugangsdaten zu {$sShopURL} lauten wie folgt:
Benutzer: {$sMail}
Passwort: {$sPassword}
EOD;

        $context = [
            'sShopURL' => 'http://demo.shopware.de',
            'sMail' => 'info@example.de',
            'sPassword' => '123muster',
        ];

        $result = $this->compiler->compileCompatibilityMode($template, $context);

        static::assertEquals($result, $expectedResult);
    }

    public function testShouldCompileSmarty(): void
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

        $sJob['articles'] = [
            [
                'ordernumber' => '111',
                'name' => 'test111',
                'instock' => '234',
                'stockmin' => '255',
            ],
            [
                'ordernumber' => '123',
                'name' => 'test123',
                'instock' => '111',
                'stockmin' => '123',
            ],
        ];

        $context = [
            'sConfig' => ['sSHOPNAME' => 'Shopware 3.5 Demo', 'sMAIL' => 'info@example.com'],
            'sJob' => $sJob,
        ];

        $result = $this->compiler->compileSmartyString($template, $context);

        static::assertEquals($result, $expectedResult);
    }

    /**
     * @depends testShouldCompileSmarty
     * @depends testShouldCompileCompatibilityMode
     */
    public function testShouldCompileMixedString(): void
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
Benutzer: info@example.de
Passwort: 123muster
EOD;
        $context = [
            'sConfig' => ['sSHOPNAME' => 'Shopware 3.5 Demo', 'sMAIL' => 'info@example.com'],
            'sShopURL' => 'http://demo.shopware.de',
            'sMail' => 'info@example.de',
            'sPassword' => '123muster',
        ];

        $result = $this->compiler->compileString($template, $context);

        static::assertEquals($result, $expectedResult);
    }

    public function testInvalidSmartyShouldThrowExceptionAndCustomExceptionMessage(): void
    {
        $this->expectException('Enlight_Exception');
        $this->expectExceptionMessage('Syntax Error 74&quot;  on line 1 &quot;Hallo {$user|invalidmodifier}&quot; unknown modifier &quot;invalidmodifier&quot');
        $defectSmartyString = 'Hallo {$user|invalidmodifier}';
        $this->compiler->compileString($defectSmartyString);
    }
}
