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

namespace Shopware\Tests\Functional\Components\Captcha;

use Enlight_Controller_Request_RequestTestCase;
use Enlight_Template_Manager;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Captcha\DefaultCaptcha;
use Shopware_Components_Config;

class DefaultCaptchaTest extends TestCase
{
    private DefaultCaptcha $captcha;

    public function setUp(): void
    {
        Shopware()->Session()->clear();

        $this->captcha = new DefaultCaptcha(
            Shopware()->Container(),
            Shopware()->Container()->get(Shopware_Components_Config::class),
            Shopware()->Container()->get(Enlight_Template_Manager::class)
        );
    }

    public function testCaptchaIsInitiallyInvalid(): void
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('sCaptcha', 'foobar');
        static::assertFalse($this->captcha->validate($request));
    }

    public function testValidCaptcha(): void
    {
        $templateData = $this->captcha->getTemplateData();
        static::assertArrayHasKey('img', $templateData);

        $random = Shopware()->Session()->get(DefaultCaptcha::SESSION_KEY);

        $request = new Enlight_Controller_Request_RequestTestCase();
        $random = array_keys($random);
        $request->setParam('sCaptcha', array_pop($random));

        static::assertTrue($this->captcha->validate($request));
    }

    public function testValidMultipleCaptchaCalls(): void
    {
        // call captcha five times
        $this->captcha->getTemplateData();
        $this->captcha->getTemplateData();
        $this->captcha->getTemplateData();
        $this->captcha->getTemplateData();
        $templateData = $this->captcha->getTemplateData();

        static::assertArrayHasKey('img', $templateData);

        $random = Shopware()->Session()->get(DefaultCaptcha::SESSION_KEY);
        static::assertCount(5, $random);

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('sCaptcha', 'INVALID CHALLENGE');
        static::assertFalse($this->captcha->validate($request));

        $random = Shopware()->Session()->get(DefaultCaptcha::SESSION_KEY);
        static::assertCount(5, $random, 'Invalid captcha should not decrease captcha backlog');

        // extract second generated captcha
        $challenge = \array_slice(array_keys($random), 1, 1)[0];
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('sCaptcha', $challenge);
        static::assertTrue($this->captcha->validate($request));

        $random = Shopware()->Session()->get(DefaultCaptcha::SESSION_KEY);
        static::assertCount(4, $random, 'Valid challenge should decrease captcha backlog');
    }
}
