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

namespace Shopware\Tests\Unit\Controllers\Backend;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\PluginInstallerBundle\Service\AccountManagerService;
use Shopware\Bundle\PluginInstallerBundle\Struct\LocaleStruct;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Tests\TestReflectionHelper;
use Shopware_Components_Auth;
use Shopware_Controllers_Backend_FirstRunWizardPluginManager;

class FirstRunWizardPluginManagerTest extends TestCase
{
    public function setUp(): void
    {
        Shopware_Controllers_Backend_FirstRunWizardPluginManager::$locales = [];
    }

    public function testGetCurrentLocaleWIthouthContent(): void
    {
        $controller = new Shopware_Controllers_Backend_FirstRunWizardPluginManager();

        $localeStruct = new LocaleStruct();
        $localeStruct->setName('foo_bar');
        $testLocale = 'bar_foo';

        $mock = $this->getMockContainer($localeStruct, $testLocale);

        $controller->setContainer($mock);

        $currentLocaleMethode = TestReflectionHelper::getMethod(\get_class($controller), 'getCurrentLocale');

        static::assertNull($currentLocaleMethode->invoke($controller));
    }

    public function testGetCurrentLocaleWithEnglish(): void
    {
        $controller = new Shopware_Controllers_Backend_FirstRunWizardPluginManager();

        $localeStruct = new LocaleStruct();
        $localeStruct->setName('en_GB');
        $testLocale = 'bar_foo';

        $mock = $this->getMockContainer($localeStruct, $testLocale);

        $controller->setContainer($mock);

        $currentLocaleMethode = TestReflectionHelper::getMethod(\get_class($controller), 'getCurrentLocale');

        static::assertSame('en_GB', $currentLocaleMethode->invoke($controller)->getName());
    }

    public function testGetCurrentLocaleWithSameContent(): void
    {
        $controller = new Shopware_Controllers_Backend_FirstRunWizardPluginManager();

        $localeStruct = new LocaleStruct();
        $localeStruct->setName('de_DE');
        $testLocale = 'de_DE';

        $mock = $this->getMockContainer($localeStruct, $testLocale);

        $controller->setContainer($mock);

        $currentLocaleMethode = TestReflectionHelper::getMethod(\get_class($controller), 'getCurrentLocale');

        static::assertSame('de_DE', $currentLocaleMethode->invoke($controller)->getName());
    }

    private function getMockContainer(LocaleStruct $localeStruct, string $testLocale): Container
    {
        $accountManagerServiceMock = $this->getMockBuilder(AccountManagerService::class)->disableOriginalConstructor()->getMock();
        $accountManagerServiceMock->method('getLocales')->willReturn([$localeStruct]);

        $auth = $this->getMockBuilder(Shopware_Components_Auth::class)->disableOriginalConstructor()->getMock();
        $auth->method('getIdentity')->willReturn(new class($testLocale) {
            /**
             * @var object
             */
            public $locale;

            public function __construct(string $testLocale)
            {
                $this->locale = new class($testLocale) {
                    /**
                     * @var string
                     */
                    public $locale;

                    public function __construct(string $testLocale)
                    {
                        $this->locale = $testLocale;
                    }

                    public function getLocale(): string
                    {
                        return $this->locale;
                    }
                };
            }
        });

        $mock = $this->getMockBuilder(Container::class)->getMock();
        $mock->method('get')->willReturnOnConsecutiveCalls($accountManagerServiceMock, $auth);

        return $mock;
    }
}
