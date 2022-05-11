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
     * @return MockObject
     */
    protected function getEntityManager()
    {
        return $this->createMock(ModelManager::class);
    }

    /**
     * @return MockObject
     */
    protected function getEventManager()
    {
        return $this->createMock(Enlight_Event_EventManager::class);
    }

    /**
     * @return MockObject
     */
    protected function getPathResolver()
    {
        return $this->createMock(PathResolver::class);
    }

    /**
     * @return MockObject
     */
    protected function getUtilClass()
    {
        return $this->createMock(Util::class);
    }

    /**
     * @return MockObject
     */
    protected function getConfigurator()
    {
        return $this->createMock(Configurator::class);
    }

    /**
     * @return MockObject
     */
    protected function getFormPersister()
    {
        return $this->createMock(ThemePersister::class);
    }

    /**
     * @return TestBareTheme
     */
    protected function getBareTheme()
    {
        require_once __DIR__ . '/Themes/TestBare/Theme.php';

        return new TestBareTheme();
    }

    /**
     * @return Theme
     */
    protected function getResponsiveTheme()
    {
        require_once __DIR__ . '/Themes/TestResponsive/Theme.php';

        return new Theme();
    }

    /**
     * @return Template
     */
    protected function getTemplate()
    {
        return $this->createMock(Template::class);
    }

    /**
     * @return MockObject
     */
    protected function getShopRepository()
    {
        return $this->createMock(Repository::class);
    }

    protected function getSnippetHandler()
    {
        return $this->createMock(DatabaseHandler::class);
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object $object     Instantiated object that we will run method on
     * @param string $methodName Method name to call
     * @param array  $parameters array of parameters to pass into method
     *
     * @return mixed method return
     */
    protected function invokeMethod(object $object, string $methodName, array $parameters = [])
    {
        return TestReflectionHelper::getMethod(\get_class($object), $methodName)->invokeArgs($object, $parameters);
    }
}
