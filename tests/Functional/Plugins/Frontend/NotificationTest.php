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

namespace Shopware\Tests\Functional\Plugins\Frontend;

use Doctrine\DBAL\Connection;
use Enlight_Components_Test_Plugin_TestCase;
use Exception;
use Shopware\Bundle\PluginInstallerBundle\Service\InstallerService;
use Shopware\Bundle\PluginInstallerBundle\Service\LegacyPluginInstaller;
use Shopware\Components\Captcha\DefaultCaptcha;
use Shopware\Components\Captcha\Exception\CaptchaNotFoundException;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class NotificationTest extends Enlight_Components_Test_Plugin_TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    public const PRODUCT_ID = 272;

    public const NOTIFY_POST_PARAMETERS = [
        'notifyOrdernumber' => 'SW10239',
        'sNotificationemail' => 'test@example.de',
    ];

    public const NOTIFICATION_ACTION_URL = 'genusswelten/koestlichkeiten/272/spachtelmasse?action=notify&number=SW10239';

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function setUp(): void
    {
        $this->getContainer()->reset(InstallerService::class);
        $this->getContainer()->reset(LegacyPluginInstaller::class);
        $pluginManager = $this->getContainer()->get(InstallerService::class);

        $pluginManager->refreshPluginList();

        $plugin = $pluginManager->getPluginByName('Notification');

        $pluginManager->installPlugin($plugin);
        $pluginManager->activatePlugin($plugin);

        $this->getContainer()->reset('plugins')->load('plugins');

        $sql = 'UPDATE s_articles SET notification = 1 WHERE id = ' . self::PRODUCT_ID;
        $this->getContainer()->get('dbal_connection')->executeQuery($sql);
        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->saveNotifyCaptcha('nocaptcha');
        parent::tearDown();
    }

    /**
     * @throws Exception
     */
    public function testValidateCaptchaWithInvalidName(): void
    {
        $this->saveNotifyCaptcha('notExistingCaptchaOption');
        $random = md5(uniqid());
        $sessionVars = ['sCaptcha' => $random];
        $this->getContainer()->get('session')->offsetSet(DefaultCaptcha::SESSION_KEY, $sessionVars);

        $postParameter = self::NOTIFY_POST_PARAMETERS;
        $postParameter['sCaptcha'] = $random;

        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);
        $this->expectException(CaptchaNotFoundException::class);
        $this->dispatch(self::NOTIFICATION_ACTION_URL);
    }

    public function testNoCaptcha(): void
    {
        $this->saveNotifyCaptcha('nocaptcha');
        $postParameter = self::NOTIFY_POST_PARAMETERS;

        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch(self::NOTIFICATION_ACTION_URL);

        $viewVariables = $this->View()->getAssign();
        static::assertArrayNotHasKey('NotifyCaptchaError', $viewVariables);
    }

    public function testHoneypot(): void
    {
        $this->saveNotifyCaptcha('honeypot');
        $postParameter = self::NOTIFY_POST_PARAMETERS;

        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch(self::NOTIFICATION_ACTION_URL);

        $viewVariables = $this->View()->getAssign();

        static::assertArrayNotHasKey('NotifyCaptchaError', $viewVariables);
    }

    public function testDefault(): void
    {
        $this->saveNotifyCaptcha('default');
        $random = md5(uniqid());
        $sessionVars = ['sCaptcha' => $random, $random => true];

        $this->getContainer()->get('session')->offsetSet(DefaultCaptcha::SESSION_KEY, $sessionVars);

        $postParameter = self::NOTIFY_POST_PARAMETERS;
        $postParameter['sCaptcha'] = $random;
        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch(self::NOTIFICATION_ACTION_URL);
        $viewVariables = $this->View()->getAssign();
        static::assertArrayNotHasKey('NotifyCaptchaError', $viewVariables);
    }

    public function testInvalidDefault(): void
    {
        $this->saveNotifyCaptcha('default');
        $random = md5(uniqid());
        $sessionVars = ['sCaptcha' => $random];

        $this->getContainer()->get('session')->offsetSet(DefaultCaptcha::SESSION_KEY, $sessionVars);

        $postParameter = self::NOTIFY_POST_PARAMETERS;
        $postParameter['sCaptcha'] = $random;

        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch(self::NOTIFICATION_ACTION_URL);

        $viewVariables = $this->View()->getAssign();
        static::assertArrayHasKey('NotifyCaptchaError', $viewVariables);
    }

    public function testInvalidHoneypot(): void
    {
        $this->saveNotifyCaptcha('honeypot');
        $postParameter = self::NOTIFY_POST_PARAMETERS;
        $postParameter['first_name_confirmation'] = uniqid();

        $this->Request()->setMethod('POST');
        $this->Request()->setPost($postParameter);

        $this->dispatch(self::NOTIFICATION_ACTION_URL);

        $viewVariables = $this->View()->getAssign();

        static::assertArrayHasKey('NotifyCaptchaError', $viewVariables);
    }

    private function saveNotifyCaptcha(string $value): void
    {
        $formattedValue = sprintf('s:%d:"%s";', \strlen($value), $value);
        $this->getContainer()->get(Connection::class)->executeQuery(
            'UPDATE s_core_config_elements SET value = ? WHERE name = ?',
            [$formattedValue, 'notificationCaptchaConfig']
        );

        $this->getContainer()->get('cache')->clean();
    }
}
