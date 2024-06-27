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

namespace Shopware\Tests\Functional\Components\Theme;

use Enlight_Components_Test_TestCase;
use Enlight_Event_EventManager;
use PHPUnit\Framework\MockObject\MockObject;
use Shopware\Components\Form\Persister\Theme as ThemePersister;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Snippet\DatabaseHandler;
use Shopware\Components\Theme\Configurator;
use Shopware\Components\Theme\PathResolver;
use Shopware\Components\Theme\Util;
use Shopware\Models\Shop\Repository;
use Shopware\Models\Shop\Template;
use Shopware\Tests\TestReflectionHelper;
use Shopware\Themes\TestBare\Theme as TestBareTheme;
use Shopware\Themes\TestResponsive\Theme;

class Base extends Enlight_Components_Test_TestCase
{
    /**
     * @return ModelManager&MockObject
     */
    protected function getEntityManager(): ModelManager
    {
        return $this->createMock(ModelManager::class);
    }

    /**
     * @return Enlight_Event_EventManager&MockObject
     */
    protected function getEventManager(): Enlight_Event_EventManager
    {
        return $this->createMock(Enlight_Event_EventManager::class);
    }

    /**
     * @return PathResolver&MockObject
     */
    protected function getPathResolver(): PathResolver
    {
        return $this->createMock(PathResolver::class);
    }

    /**
     * @return Util&MockObject
     */
    protected function getUtilClass(): Util
    {
        return $this->createMock(Util::class);
    }

    protected function getConfigurator(): Configurator
    {
        return $this->createMock(Configurator::class);
    }

    protected function getFormPersister(): ThemePersister
    {
        return $this->createMock(ThemePersister::class);
    }

    protected function getBareTheme(): TestBareTheme
    {
        require_once __DIR__ . '/Themes/TestBare/Theme.php';

        return new TestBareTheme();
    }

    protected function getResponsiveTheme(): Theme
    {
        require_once __DIR__ . '/Themes/TestResponsive/Theme.php';

        return new Theme();
    }

    protected function getTemplate(): Template
    {
        return $this->createMock(Template::class);
    }

    protected function getShopRepository(): Repository
    {
        return $this->createMock(Repository::class);
    }

    protected function getSnippetHandler(): DatabaseHandler
    {
        return $this->createMock(DatabaseHandler::class);
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object      $object     Instantiated object that we will run method on
     * @param string      $methodName Method name to call
     * @param list<mixed> $parameters array of parameters to pass into method
     *
     * @return mixed method return
     */
    protected function invokeMethod(object $object, string $methodName, array $parameters = [])
    {
        return TestReflectionHelper::getMethod(\get_class($object), $methodName)->invokeArgs($object, $parameters);
    }
}
