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

namespace Shopware\Recovery\Tests\Install\Command;

use Generator;
use PHPUnit\Framework\TestCase;
use Shopware\Recovery\Common\Middelware\XssMiddleware;
use Shopware\Recovery\Tests\Traits\InstallCommandTestTrait;
use Slim\Slim;

class XssMiddlewareTest extends TestCase
{
    use InstallCommandTestTrait;

    /**
     * @dataProvider createInputData
     */
    public function testXssMiddleware($slimInputOriginal, $slimInput): void
    {
        $settings = [
            'slim.input' => $slimInputOriginal,
        ];

        $env = \Slim\Environment::mock($settings);

        $app = new Slim();
        $app->environment = $env;

        $xssMiddleware = new XssMiddleware();
        $xssMiddleware->setApplication($app);
        $xssMiddleware->setNextMiddleware($app);
        $xssMiddleware->call();

        static::assertEquals($slimInput, $app->environment['slim.input']);
    }

    public static function createInputData(): Generator
    {
        yield 'No xss' => [
            'adc',
            'adc',
        ];

        yield 'Xss' => [
            '/><script>alert(1)</script>',
            '/>',
        ];
    }
}
