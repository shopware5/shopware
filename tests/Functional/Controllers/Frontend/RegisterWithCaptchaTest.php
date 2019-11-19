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

namespace Shopware\Tests\Functional\Controllers\Frontend;

use Shopware\Components\Captcha\DefaultCaptcha;

class RegisterWithCaptchaTest extends \Enlight_Components_Test_Plugin_TestCase
{
    public static function tearDownAfterClass(): void
    {
        static::saveConfig('registerCaptcha', 'nocaptcha');
    }

    public function testValidateCaptchaIsUninstalled()
    {
        static::saveConfig('registerCaptcha', 'uninstalledCaptchaName');
        $postParameter = include __DIR__ . '/fixtures/captchaRequest.php';
        $this->Request()->setHeader('User-Agent', include __DIR__ . '/fixtures/UserAgent.php');
        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch('/register/saveRegister/sTarget/account/sTargetAction/index');

        $viewVariables = $this->View()->getAssign();

        static::assertTrue($this->Response()->isRedirect());
        static::assertArrayNotHasKey('errors', $viewVariables);
    }

    public function testNoCaptcha()
    {
        static::saveConfig('registerCaptcha', 'nocaptcha');
        $postParameter = include __DIR__ . '/fixtures/captchaRequest.php';

        $this->Request()->setHeader('User-Agent', include __DIR__ . '/fixtures/UserAgent.php');
        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch('/register/saveRegister/sTarget/account/sTargetAction/index');

        $viewVariables = $this->View()->getAssign();

        static::assertTrue($this->Response()->isRedirect());
        static::assertArrayNotHasKey('errors', $viewVariables);
    }

    public function testHoneypot()
    {
        static::saveConfig('registerCaptcha', 'honeypot');
        $postParameter = include __DIR__ . '/fixtures/captchaRequest.php';

        $this->Request()->setHeader('User-Agent', include __DIR__ . '/fixtures/UserAgent.php');
        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch('/register/saveRegister/sTarget/account/sTargetAction/index');

        $viewVariables = $this->View()->getAssign();

        static::assertTrue($this->Response()->isRedirect());
        static::assertArrayNotHasKey('errors', $viewVariables);
    }

    public function testDefault()
    {
        static::saveConfig('registerCaptcha', 'default');
        $random = md5(uniqid());
        $sessionVars = ['sCaptcha' => $random, $random => true];

        Shopware()->Session()->offsetSet(DefaultCaptcha::SESSION_KEY, $sessionVars);

        $postParameter = include __DIR__ . '/fixtures/captchaRequest.php';
        $postParameter['sCaptcha'] = $random;

        $this->Request()->setHeader('User-Agent', include __DIR__ . '/fixtures/UserAgent.php');
        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch('/register/saveRegister/sTarget/account/sTargetAction/index');

        $viewVariables = $this->View()->getAssign();

        static::assertTrue($this->Response()->isRedirect());
        static::assertArrayNotHasKey('errors', $viewVariables);
    }

    public function testInvalidHoneypot()
    {
        static::saveConfig('registerCaptcha', 'honeypot');
        $postParameter = include __DIR__ . '/fixtures/captchaRequest.php';
        $postParameter['first_name_confirmation'] = uniqid();

        $this->Request()->setHeader('User-Agent', include __DIR__ . '/fixtures/UserAgent.php');
        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch('/register/saveRegister/sTarget/account/sTargetAction/index');

        $viewVariables = $this->View()->getAssign();

        static::assertArrayHasKey('errors', $viewVariables);
    }

    public function testInvalidDefault()
    {
        static::saveConfig('registerCaptcha', 'default');
        $random = md5(uniqid());
        $sessionVars = ['sCaptcha' => $random];

        Shopware()->Session()->offsetSet(DefaultCaptcha::SESSION_KEY, $sessionVars);

        $postParameter = include __DIR__ . '/fixtures/captchaRequest.php';
        $postParameter['sCaptcha'] = $random;

        $this->Request()->setHeader('User-Agent', include __DIR__ . '/fixtures/UserAgent.php');
        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch('/register/saveRegister/sTarget/account/sTargetAction/index');

        $viewVariables = $this->View()->getAssign();

        static::assertArrayHasKey('errors', $viewVariables);
    }

    private static function saveConfig($name, $value)
    {
        $formattedValue = sprintf('s:%d:"%s";', strlen($value), $value);
        Shopware()->Db()->query(
            'UPDATE s_core_config_elements SET value = ? WHERE name = ?',
            [$formattedValue, $name]
        );
        Shopware()->Container()->get('cache')->clean();
    }
}
