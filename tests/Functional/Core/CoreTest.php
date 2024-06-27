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

namespace Shopware\Tests\Functional\Core;

use Doctrine\DBAL\Connection;
use Enlight_Components_Test_Controller_TestCase;
use sCore;
use Shopware\Tests\Functional\Traits\ContainerTrait;

class CoreTest extends Enlight_Components_Test_Controller_TestCase
{
    use ContainerTrait;

    private sCore $module;

    public function setUp(): void
    {
        parent::setUp();
        $this->module = $this->getContainer()->get('modules')->Core();
    }

    /**
     * @covers \sCore::sBuildLink
     */
    public function testsBuildLink(): void
    {
        // Empty data will return empty string
        $request = $this->Request()->setParams([]);
        $this->Front()->setRequest($request);
        static::assertSame('', $this->module->sBuildLink([]));

        // Provided sVariables are passed into the url, except 'coreID' and 'sPartner'
        $sVariablesTestResult = $this->module->sBuildLink([
            'coreID' => 'foo',
            'sPartner' => 'bar',
            'some' => 'with',
            'other' => 'test',
            'variables' => 'values',
        ]);
        static::assertIsString($sVariablesTestResult);
        static::assertGreaterThan(0, \strlen($sVariablesTestResult));

        $resultArray = [];
        parse_str(trim($sVariablesTestResult, '?'), $resultArray);
        static::assertArrayHasKey('some', $resultArray);
        static::assertArrayHasKey('other', $resultArray);
        static::assertArrayHasKey('variables', $resultArray);
        static::assertArrayNotHasKey('coreID', $resultArray);
        static::assertArrayNotHasKey('sPartner', $resultArray);
        static::assertSame('with', $resultArray['some']);
        static::assertSame('test', $resultArray['other']);
        static::assertSame('values', $resultArray['variables']);

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
        static::assertGreaterThan(0, \strlen($sVariablesTestResult));

        $resultArray = [];
        parse_str(trim($sVariablesTestResult, '?'), $resultArray);
        static::assertArrayHasKey('just', $resultArray);
        static::assertArrayHasKey('some', $resultArray);
        static::assertArrayHasKey('other', $resultArray);
        static::assertArrayHasKey('variables', $resultArray);
        static::assertArrayNotHasKey('nullVariables', $resultArray);
        static::assertArrayNotHasKey('nullGet', $resultArray);
        static::assertSame('used', $resultArray['just']);
        static::assertSame('for', $resultArray['some']);
        static::assertSame('with', $resultArray['other']);
        static::assertSame('values', $resultArray['variables']);

        // Test that sViewport=cat only keeps sCategory and sPage from GET
        // Test that they can still be overwritten by sVariables
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
        static::assertGreaterThan(0, \strlen($sVariablesTestResult));

        $resultArray = [];
        parse_str(trim($sVariablesTestResult, '?'), $resultArray);
        static::assertArrayHasKey('sViewport', $resultArray);
        static::assertArrayHasKey('sCategory', $resultArray);
        static::assertArrayHasKey('sPage', $resultArray);
        static::assertArrayHasKey('other', $resultArray);
        static::assertArrayHasKey('variables', $resultArray);
        static::assertArrayNotHasKey('foo', $resultArray);
        static::assertSame('cat', $resultArray['sViewport']);
        static::assertSame('sVariablesCategory', $resultArray['sCategory']);
        static::assertSame('getPage', $resultArray['sPage']);
        static::assertSame('with', $resultArray['other']);
        static::assertSame('values', $resultArray['variables']);

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
        static::assertGreaterThan(0, \strlen($sVariablesTestResult));

        $resultArray = [];
        parse_str(trim($sVariablesTestResult, '?'), $resultArray);
        static::assertArrayHasKey('sViewport', $resultArray);
        static::assertArrayHasKey('sCategory', $resultArray);
        static::assertArrayHasKey('sPage', $resultArray);
        static::assertArrayHasKey('other', $resultArray);
        static::assertArrayHasKey('variables', $resultArray);
        static::assertArrayNotHasKey('foo', $resultArray);
        static::assertSame('test', $resultArray['sViewport']);
        static::assertSame('sVariablesCategory', $resultArray['sCategory']);
        static::assertSame('getPage', $resultArray['sPage']);
        static::assertSame('with', $resultArray['other']);
        static::assertSame('values', $resultArray['variables']);
    }

    /**
     * @covers \sCore::sRewriteLink
     */
    public function testsRewriteLink(): void
    {
        // Call dispatch as we need the Router to be available inside sCore
        $this->dispatch('/');

        $baseUrl = $this->module->sRewriteLink();

        // Without arguments, we expect the base url
        static::assertIsString($baseUrl);
        static::assertGreaterThan(0, \strlen($baseUrl));

        $connection = $this->getContainer()->get(Connection::class);
        $shopId = $this->getContainer()->get('shop')->getId();
        // Fetch all rows and test them
        $paths = $connection->fetchFirstColumn(
            'SELECT org_path FROM s_core_rewrite_urls WHERE subshopID = ?',
            [$shopId]
        );
        foreach ($paths as $path) {
            $expectedPath = $connection->fetchOne(
                'SELECT path FROM s_core_rewrite_urls WHERE subshopID = ? AND org_path = ? ORDER BY main DESC LIMIT 1',
                [$shopId, $path]
            );

            static::assertSame(strtolower($baseUrl . $expectedPath), $this->module->sRewriteLink('?' . $path));
            static::assertSame(strtolower($baseUrl . $expectedPath), $this->module->sRewriteLink('?' . $path, 'testTitle'));
        }
    }
}
