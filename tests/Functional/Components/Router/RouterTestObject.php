<?php declare(strict_types=1);
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

namespace Functional\Components\Router;

use Enlight_Components_Test_TestCase;
use InvalidArgumentException;
use Shopware\Components\Routing\Context;
use stdClass;

class RouterTestObject extends Enlight_Components_Test_TestCase
{
    protected $errorHandler;

    /**
     * @before
     */
    public function prepareUserAsException(): void
    {
        $this->errorHandler = set_error_handler(static function (int $errno, string $errstr) {
            throw new InvalidArgumentException($errstr, $errno);
        }, E_USER_DEPRECATED);
    }

    /**
     * @after
     */
    public function restoreErrorHandler(): void
    {
        restore_error_handler();
    }

    /**
     * tests for passing an object to assemble
     */
    public function testArrayParamsWithObject(): void
    {
        $router = Shopware()->Container()->get('router');
        $localRouter = clone $router;

        $context = new Context();
        $context->setShopId(1);
        $localRouter->setContext($context);

        $cls = new stdClass();
        $cls->test = 'It\'s a class';

        $this->expectException(\InvalidArgumentException::class);

        $url = $localRouter->assemble([$cls]);
    }
}
