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

namespace Shopware\Tests\Functional\Components\Privacy;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Privacy\IpAnonymizer;

class IpAnonymizerTest extends TestCase
{
    public function testIpAnonymizer()
    {
        $tests = [
            '207.142.131.005' => '207.142.0.0',
            '207.142.131.5' => '207.142.0.0',
            '2a00:1450:4001:816::200e' => '2a00:1450:4001:::',
            '2001:0db8:0000:08d3:0000:8a2e:0070:7344' => '2001:0db8:0000:08d3:0000:::',
            '2001:0db8:0000:08d3:0000:8a2e:0070:734a' => '2001:0db8:0000:08d3:0000:::',
            '2001:db8::8d3::8a2e:70:734a' => '2001:db8::8d3::::',
            '2001:0db8::8d3::8a2e:7:7344' => '2001:0db8::8d3::::',
            '::1' => '::',
            '127.0.0.1' => '127.0.0.0',
        ];

        $anonymizer = new IpAnonymizer();

        foreach ($tests as $ip => $expected) {
            static::assertEquals($expected, $anonymizer->anonymize($ip));
        }
    }
}
