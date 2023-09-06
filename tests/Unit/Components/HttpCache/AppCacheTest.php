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

namespace Shopware\Tests\Unit\Components\HttpCache;

use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use PHPUnit\Framework\TestCase;
use Shopware\Components\HttpCache\AppCache;
use Shopware\Kernel;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppCacheTest extends TestCase
{
    public function testCheckSltCookie(): void
    {
        $kernel = $this->getMockBuilder(Kernel::class)->disableOriginalConstructor()->getMock();
        $kernel->method('getCacheDir')->willReturn('\tmp');
        $kernel->method('getContainer')->willReturn($this->getMockBuilder(ContainerInterface::class)->disableOriginalConstructor()->getMock());
        $kernel->method('handle')->willReturn(new Enlight_Controller_Response_ResponseTestCase());

        $appCache = new AppCache(
            $kernel,
            [
                'ignored_url_parameters' => [],
                'lookup_optimization' => true,
            ]
        );

        $request = new Enlight_Controller_Request_RequestTestCase(
            [],
            [],
            [],
            [],
            [],
            [
                'REQUEST_URI' => '/backend/',
            ],
        );

        $request->setCookie('slt', '');

        $appCache->handle($request);

        static::assertEquals('slt', $request->cookies->get('nocache'));
    }
}
