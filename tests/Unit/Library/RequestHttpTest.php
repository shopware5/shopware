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

namespace Shopware\Tests\Unit\Library;

use PHPUnit\Framework\TestCase;
use Shopware\Tests\Functional\Helper\Utils;

class RequestHttpTest extends TestCase
{
    private $request;

    public function setUp(): void
    {
        $this->request = new \Enlight_Controller_Request_RequestHttp();
    }

    /**
     * @dataProvider getDataParamHandling
     */
    public function testGetParamHandling($filled, $default, $expect): void
    {
        Utils::hijackProperty($this->request, '_params', ['foo' => $filled]);

        static::assertEquals($expect, $this->request->getParam('foo', $default));

        $this->request->request->set('foo', $filled);
        static::assertEquals($expect, $this->request->getParam('foo', $default));

        $this->request->query->set('foo', $filled);
        static::assertEquals($expect, $this->request->getParam('foo', $default));
    }

    public function getDataParamHandling(): array
    {
        return [
            [
                null,
                'foo',
                'foo',
            ], [
                false,
                'foo',
                false,
            ], [
                true,
                'foo',
                true,
            ], [
                0,
                'foo',
                0,
            ], [
                '0',
                'foo',
                '0',
            ], [
                'false',
                'foo',
                'false',
            ], [
                'true',
                'foo',
                'true',
            ], [
                5.5,
                'foo',
                5.5,
            ],
        ];
    }
}
