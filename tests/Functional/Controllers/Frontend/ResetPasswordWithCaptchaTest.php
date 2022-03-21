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

use Doctrine\DBAL\Connection;
use Enlight_Components_Test_Plugin_TestCase;
use Shopware\Components\Captcha\DefaultCaptcha;
use Shopware\Components\Captcha\Exception\CaptchaNotFoundException;
use Shopware\Tests\Functional\Traits\ContainerTrait;

class ResetPasswordWithCaptchaTest extends Enlight_Components_Test_Plugin_TestCase
{
    use ContainerTrait;

    private const PASSWORD_RESET_REQUEST = [
        'module' => 'frontend',
        'controller' => 'account',
        'action' => 'password',
        'email' => 'test@example.com',
        'first_name_confirmation' => '',
    ];

    private const USER_AGENT = 'Mozilla/5.0 (Android; Tablet; rv:14.0) Gecko/14.0 Firefox/14.0';

    public function tearDown(): void
    {
        $this->Template()->clearAllAssign();
        $this->savePasswordResetCaptchaConfig('nocaptcha');
        parent::tearDown();
    }

    public function testValidateCaptchaWithInvalidName(): void
    {
        $this->savePasswordResetCaptchaConfig('uninstalledCaptchaName');
        $postParameter = self::PASSWORD_RESET_REQUEST;
        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);
        $this->expectException(CaptchaNotFoundException::class);
        $this->dispatch('/account/password');
    }

    public function testNoCaptcha(): void
    {
        $this->savePasswordResetCaptchaConfig('nocaptcha');
        $postParameter = self::PASSWORD_RESET_REQUEST;

        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch('/account/password');

        $viewVariables = $this->View()->getAssign();
        static::assertArrayNotHasKey('sErrorMessages', $viewVariables);
    }

    public function testHoneypot(): void
    {
        $this->savePasswordResetCaptchaConfig('honeypot');
        $postParameter = self::PASSWORD_RESET_REQUEST;

        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch('/account/password');

        $viewVariables = $this->View()->getAssign();

        static::assertArrayNotHasKey('sErrorMessages', $viewVariables);
    }

    public function testDefault(): void
    {
        $this->savePasswordResetCaptchaConfig('default');
        $random = md5(uniqid());
        $sessionVars = ['sCaptcha' => $random, $random => true];

        Shopware()->Session()->offsetSet(DefaultCaptcha::SESSION_KEY, $sessionVars);

        $postParameter = self::PASSWORD_RESET_REQUEST;
        $postParameter['sCaptcha'] = $random;
        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch('/account/password');
        $viewVariables = $this->View()->getAssign();
        static::assertArrayNotHasKey('sErrorMessages', $viewVariables);
    }

    public function testInvalidDefault(): void
    {
        $this->savePasswordResetCaptchaConfig('default');
        $random = md5(uniqid());
        $sessionVars = ['sCaptcha' => $random];

        Shopware()->Session()->offsetSet(DefaultCaptcha::SESSION_KEY, $sessionVars);

        $postParameter = self::PASSWORD_RESET_REQUEST;
        $postParameter['sCaptcha'] = $random;

        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch('/account/password');

        $viewVariables = $this->View()->getAssign();
        static::assertArrayHasKey('sErrorMessages', $viewVariables);
    }

    public function testInvalidHoneypot(): void
    {
        $this->savePasswordResetCaptchaConfig('honeypot');
        $postParameter = self::PASSWORD_RESET_REQUEST;
        $postParameter['first_name_confirmation'] = uniqid();

        $this->Request()->setHeader('User-Agent', self::USER_AGENT);
        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch('/account/password');

        $viewVariables = $this->View()->getAssign();

        static::assertArrayHasKey('sErrorMessages', $viewVariables);
    }

    private function savePasswordResetCaptchaConfig(string $value): void
    {
        $formattedValue = sprintf('s:%d:"%s";', \strlen($value), $value);
        $this->getContainer()->get(Connection::class)->executeQuery(
            'UPDATE s_core_config_elements SET value = ? WHERE name = ?',
            [$formattedValue, 'passwordResetCaptcha']
        );

        Shopware()->Container()->get('cache')->clean();
    }
}
