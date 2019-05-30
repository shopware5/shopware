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

namespace Shopware\Tests\Functional\Components;

use Enlight_Components_Test_TestCase;
use Shopware\Components\StreamProtocolValidator;

class StreamProtocolValidatorTest extends Enlight_Components_Test_TestCase
{
    public function testDefaultProtocols()
    {
        $urls = [
            'ftp://user:password@somehost.com/path',
            'ftps://user:password@somehost.com/path',
            'http://user:password@somehost.com/path?query=foo',
            'https://user:password@somehost.com/path?query=foo',
            'file:/foo/bar/baz',
            '/foo/bar/baz',
        ];

        $validator = new StreamProtocolValidator();

        foreach ($urls as $url) {
            static::assertEquals(true, $validator->validate($url));
        }
    }

    public function testInvalidProtocols()
    {
        $urls = [
            'phar://user:password@somehost.com/path',
            'zip://user:password@somehost.com/path',
            'phar://user:password@somehost.com/path?query=foo',
            'zip://user:password@somehost.com/path?query=foo',
            'phar:/foo/bar/baz',
            'zip:/foo/bar/baz',
        ];

        $validator = new StreamProtocolValidator();

        foreach ($urls as $url) {
            try {
                $validator->validate($url);
            } catch (\InvalidArgumentException $ex) {
                static::assertContains('Invalid stream protocol', $ex->getMessage());
                continue;
            }

            static::assertEquals('Missing exception for url ', $url);
        }
    }
}
