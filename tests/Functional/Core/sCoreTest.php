<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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

class sCoreTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * @var sCore
     */
    private $module;

    public function setUp(): void
    {
        $this->module = Shopware()->Modules()->Core();
    }

    /**
     * @covers \sCore::sBuildLink
     */
    public function testsBuildLink()
    {
        // Empty data will return empty string
        $request = $this->Request()->setParams([]);
        $this->Front()->setRequest($request);
        static::assertEquals('', $this->module->sBuildLink([]));

        // Provided sVariables are passed into the url, except 'coreID' and 'sPartner'
        $sVariablesTestResult = $this->module->sBuildLink([
            'coreID' => 'foo',
            'sPartner' => 'bar',
            'some' => 'with',
            'other' => 'test',
            'variables' => 'values',
        ]);
        static::assertIsString($sVariablesTestResult);
        static::assertGreaterThan(0, strlen($sVariablesTestResult));

        $resultArray = [];
        parse_str(trim($sVariablesTestResult, '?'), $resultArray);
        static::assertArrayHasKey('some', $resultArray);
        static::assertArrayHasKey('other', $resultArray);
        static::assertArrayHasKey('variables', $resultArray);
        static::assertArrayNotHasKey('coreID', $resultArray);
        static::assertArrayNotHasKey('sPartner', $resultArray);
        static::assertEquals('with', $resultArray['some']);
        static::assertEquals('test', $resultArray['other']);
        static::assertEquals('values', $resultArray['variables']);

        // Provided sVariables override _GET, not overlapping get included from both
        // Also test that null values don't get passed on
        $request = $this->Request()->setParams([
            'just' => 'used',
            'some' => 'for',
            'variables' => 'testing',
            'nullGet' => null,
        ]);
        $this->Front()->setRequest($request);
        $sVariablesTestResult = $this->module->sBuildLink([
            'other' => 'with',
            'variables' => 'values',
            'nullVariables' => null,
        ]);
        static::assertIsString($sVariablesTestResult);
        static::assertGreaterThan(0, strlen($sVariablesTestResult));

        $resultArray = [];
        parse_str(trim($sVariablesTestResult, '?'), $resultArray);
        static::assertArrayHasKey('just', $resultArray);
        static::assertArrayHasKey('some', $resultArray);
        static::assertArrayHasKey('other', $resultArray);
        static::assertArrayHasKey('variables', $resultArray);
        static::assertArrayNotHasKey('nullVariables', $resultArray);
        static::assertArrayNotHasKey('nullGet', $resultArray);
        static::assertEquals('used', $resultArray['just']);
        static::assertEquals('for', $resultArray['some']);
        static::assertEquals('with', $resultArray['other']);
        static::assertEquals('values', $resultArray['variables']);

        // Test that sViewport=cat only keeps sCategory and sPage from GET
        // Test that they can still be overwriten by sVariables
        $request = $this->Request()->setParams([
            'sViewport' => 'cat',
            'sCategory' => 'getCategory',
            'sPage' => 'getPage',
            'foo' => 'bar',
        ]);
        $this->Front()->setRequest($request);
        $sVariablesTestResult = $this->module->sBuildLink([
            'sCategory' => 'sVariablesCategory',
            'other' => 'with',
            'variables' => 'values',
        ]);
        static::assertIsString($sVariablesTestResult);
        static::assertGreaterThan(0, strlen($sVariablesTestResult));

        $resultArray = [];
        parse_str(trim($sVariablesTestResult, '?'), $resultArray);
        static::assertArrayHasKey('sViewport', $resultArray);
        static::assertArrayHasKey('sCategory', $resultArray);
        static::assertArrayHasKey('sPage', $resultArray);
        static::assertArrayHasKey('other', $resultArray);
        static::assertArrayHasKey('variables', $resultArray);
        static::assertArrayNotHasKey('foo', $resultArray);
        static::assertEquals('cat', $resultArray['sViewport']);
        static::assertEquals('sVariablesCategory', $resultArray['sCategory']);
        static::assertEquals('getPage', $resultArray['sPage']);
        static::assertEquals('with', $resultArray['other']);
        static::assertEquals('values', $resultArray['variables']);

        // Test that overriding sViewport doesn't override the special behavior
        $request = $this->Request()->setParams([
            'sViewport' => 'cat',
            'sCategory' => 'getCategory',
            'sPage' => 'getPage',
            'foo' => 'boo',
        ]);
        $this->Front()->setRequest($request);
        $sVariablesTestResult = $this->module->sBuildLink([
            'sViewport' => 'test',
            'sCategory' => 'sVariablesCategory',
            'other' => 'with',
            'variables' => 'values',
        ]);
        static::assertIsString($sVariablesTestResult);
        static::assertGreaterThan(0, strlen($sVariablesTestResult));

        $resultArray = [];
        parse_str(trim($sVariablesTestResult, '?'), $resultArray);
        static::assertArrayHasKey('sViewport', $resultArray);
        static::assertArrayHasKey('sCategory', $resultArray);
        static::assertArrayHasKey('sPage', $resultArray);
        static::assertArrayHasKey('other', $resultArray);
        static::assertArrayHasKey('variables', $resultArray);
        static::assertArrayNotHasKey('foo', $resultArray);
        static::assertEquals('test', $resultArray['sViewport']);
        static::assertEquals('sVariablesCategory', $resultArray['sCategory']);
        static::assertEquals('getPage', $resultArray['sPage']);
        static::assertEquals('with', $resultArray['other']);
        static::assertEquals('values', $resultArray['variables']);
    }

    /**
     * @covers \sCore::sRewriteLink
     */
    public function testsRewriteLink()
    {
        // Call dispatch as we need the Router to be available inside sCore
        $this->dispatch('/');

        $baseUrl = $this->module->sRewriteLink();

        // Without arguments, we expect the base url
        static::assertIsString($baseUrl);
        static::assertGreaterThan(0, strlen($baseUrl));

        // Fetch all rows and test them
        $paths = Shopware()->Db()->fetchCol(
            'SELECT org_path FROM s_core_rewrite_urls WHERE subshopID = ?',
            [Shopware()->Shop()->getId()]
        );
        foreach ($paths as $path) {
            $expectedPath = Shopware()->Db()->fetchOne(
                'SELECT path FROM s_core_rewrite_urls WHERE subshopID = ? AND org_path = ? ORDER BY main DESC LIMIT 1',
                [Shopware()->Shop()->getId(), $path]
            );

            static::assertEquals(strtolower($baseUrl . $expectedPath), $this->module->sRewriteLink('?' . $path));
            static::assertEquals(strtolower($baseUrl . $expectedPath), $this->module->sRewriteLink('?' . $path, 'testTitle'));
        }
    }
}
