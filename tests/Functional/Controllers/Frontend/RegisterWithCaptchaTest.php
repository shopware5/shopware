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

namespace Shopware\Tests\Functional\Controllers\Frontend;

use Doctrine\DBAL\Connection;
use Enlight_Components_Test_Plugin_TestCase;
use Shopware\Components\Captcha\DefaultCaptcha;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class RegisterWithCaptchaTest extends Enlight_Components_Test_Plugin_TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private const USER_AGENT = 'Mozilla/5.0 (Android; Tablet; rv:14.0) Gecko/14.0 Firefox/14.0';

    public function tearDown(): void
    {
        $this->Template()->clearAllAssign();
        $this->saveRegisterCaptchaConfig('nocaptcha');
        parent::tearDown();
    }

    public function testValidateCaptchaWithInvalidName(): void
    {
        $this->saveRegisterCaptchaConfig('uninstalledCaptchaName');
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
        $this->saveRegisterCaptchaConfig('nocaptcha');
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
        $this->saveRegisterCaptchaConfig('honeypot');
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
        $this->saveRegisterCaptchaConfig('default');
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
        $this->saveRegisterCaptchaConfig('honeypot');
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
        $this->saveRegisterCaptchaConfig('default');
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

    private function saveRegisterCaptchaConfig(string $value): void
    {
        $formattedValue = sprintf('s:%d:"%s";', \strlen($value), $value);
        $this->getContainer()->get(Connection::class)->executeQuery(
            'UPDATE s_core_config_elements SET value = ? WHERE name = ?',
            [$formattedValue, 'registerCaptcha']
        );

        Shopware()->Container()->get('cache')->clean();
    }
}
