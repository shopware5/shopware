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

namespace Shopware\Tests\Functional\Controllers\Frontend;

use Enlight_Components_Test_Plugin_TestCase;
use Shopware\Components\Captcha\DefaultCaptcha;

class RegisterWithCaptchaTest extends Enlight_Components_Test_Plugin_TestCase
{
    private const USER_AGENT = 'Mozilla/5.0 (Android; Tablet; rv:14.0) Gecko/14.0 Firefox/14.0';

    public static function tearDownAfterClass(): void
    {
        self::saveRegisterCaptchaConfig('nocaptcha');
    }

    public function testValidateCaptchaIsUninstalled(): void
    {
        self::saveRegisterCaptchaConfig('uninstalledCaptchaName');
        $postParameter = include __DIR__ . '/fixtures/captchaRequest.php';
        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch('/register/saveRegister/sTarget/account/sTargetAction/index');

        $viewVariables = $this->View()->getAssign();

        static::assertTrue($this->Response()->isRedirect());
        static::assertArrayNotHasKey('errors', $viewVariables);
    }

    public function testNoCaptcha(): void
    {
        self::saveRegisterCaptchaConfig('nocaptcha');
        $postParameter = include __DIR__ . '/fixtures/captchaRequest.php';

        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch('/register/saveRegister/sTarget/account/sTargetAction/index');

        $viewVariables = $this->View()->getAssign();

        static::assertTrue($this->Response()->isRedirect());
        static::assertArrayNotHasKey('errors', $viewVariables);
    }

    public function testHoneypot(): void
    {
        self::saveRegisterCaptchaConfig('honeypot');
        $postParameter = include __DIR__ . '/fixtures/captchaRequest.php';

        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch('/register/saveRegister/sTarget/account/sTargetAction/index');

        $viewVariables = $this->View()->getAssign();

        static::assertTrue($this->Response()->isRedirect());
        static::assertArrayNotHasKey('errors', $viewVariables);
    }

    public function testDefault(): void
    {
        self::saveRegisterCaptchaConfig('default');
        $random = md5(uniqid());
        $sessionVars = ['sCaptcha' => $random, $random => true];

        Shopware()->Session()->offsetSet(DefaultCaptcha::SESSION_KEY, $sessionVars);

        $postParameter = include __DIR__ . '/fixtures/captchaRequest.php';
        $postParameter['sCaptcha'] = $random;

        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch('/register/saveRegister/sTarget/account/sTargetAction/index');

        $viewVariables = $this->View()->getAssign();

        static::assertTrue($this->Response()->isRedirect());
        static::assertArrayNotHasKey('errors', $viewVariables);
    }

    public function testInvalidHoneypot(): void
    {
        self::saveRegisterCaptchaConfig('honeypot');
        $postParameter = include __DIR__ . '/fixtures/captchaRequest.php';
        $postParameter['first_name_confirmation'] = uniqid();

        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch('/register/saveRegister/sTarget/account/sTargetAction/index');

        $viewVariables = $this->View()->getAssign();

        static::assertArrayHasKey('errors', $viewVariables);
    }

    public function testInvalidDefault(): void
    {
        self::saveRegisterCaptchaConfig('default');
        $random = md5(uniqid());
        $sessionVars = ['sCaptcha' => $random];

        Shopware()->Session()->offsetSet(DefaultCaptcha::SESSION_KEY, $sessionVars);

        $postParameter = include __DIR__ . '/fixtures/captchaRequest.php';
        $postParameter['sCaptcha'] = $random;

        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch('/register/saveRegister/sTarget/account/sTargetAction/index');

        $viewVariables = $this->View()->getAssign();

        static::assertArrayHasKey('errors', $viewVariables);
    }

    private static function saveRegisterCaptchaConfig(string $value): void
    {
        $formattedValue = sprintf('s:%d:"%s";', \strlen($value), $value);
        Shopware()->Db()->query(
            'UPDATE s_core_config_elements SET value = ? WHERE name = ?',
            [$formattedValue, 'registerCaptcha']
        );
        Shopware()->Container()->get('cache')->clean();
    }
}
