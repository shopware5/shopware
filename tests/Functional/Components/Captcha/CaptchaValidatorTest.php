<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Components\Captcha;

use Enlight_Controller_Request_RequestTestCase;
use Enlight_Template_Manager;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Captcha\CaptchaValidator;
use Shopware\Components\Captcha\DefaultCaptcha;
use Shopware_Components_Config;

class CaptchaValidatorTest extends TestCase
{
    private DefaultCaptcha $captcha;

    public function setUp(): void
    {
        $this->captcha = new DefaultCaptcha(
            Shopware()->Container(),
            Shopware()->Container()->get(Shopware_Components_Config::class),
            Shopware()->Container()->get(Enlight_Template_Manager::class)
        );
    }

    public function testValidateCustomCaptchaHoneypot(): void
    {
        /** @var CaptchaValidator $validator */
        $validator = Shopware()->Container()->get('shopware.captcha.validator');
        $honeypotParams = include __DIR__ . '/fixtures/honeypotRequest.php';

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams($honeypotParams);

        static::assertTrue($validator->validateByName($honeypotParams['captchaName'], $request));
    }

    public function testValidateCustomCaptchaDefault(): void
    {
        $this->captcha->getTemplateData();

        /** @var CaptchaValidator $validator */
        $validator = Shopware()->Container()->get('shopware.captcha.validator');
        $defaultParam = include __DIR__ . '/fixtures/honeypotRequest.php';
        $defaultParam['captchaName'] = 'default';

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams($defaultParam);

        $random = Shopware()->Session()->get(DefaultCaptcha::SESSION_KEY);
        $random = array_keys($random);
        $request->setParam('sCaptcha', array_pop($random));

        static::assertTrue($validator->validateByName($defaultParam['captchaName'], $request));
    }

    public function testInvalidCaptcha(): void
    {
        $this->captcha->getTemplateData();

        /** @var CaptchaValidator $validator */
        $validator = Shopware()->Container()->get('shopware.captcha.validator');
        $defaultParam = include __DIR__ . '/fixtures/honeypotRequest.php';
        $defaultParam['captchaName'] = 'default';

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams($defaultParam);

        // set a random false parameter
        $request->setParam('sCaptcha', uniqid());

        static::assertFalse($validator->validateByName($defaultParam['captchaName'], $request));
    }
}
