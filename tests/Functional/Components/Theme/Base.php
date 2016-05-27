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

namespace Shopware\Tests\Components\Theme;

/**
 * Class Shopware_Tests_Components_Theme_Base
 */
class Base extends \Enlight_Components_Test_TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEntityManager()
    {
        return $this->getMockBuilder('Shopware\Components\Model\ModelManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEventManager()
    {
        return $this->getMockBuilder('Enlight_Event_EventManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPathResolver()
    {
        return $this->getMockBuilder('Shopware\Components\Theme\PathResolver')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getUtilClass()
    {
        return $this->getMockBuilder('Shopware\Components\Theme\Util')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getConfigurator()
    {
        return $this->getMockBuilder('Shopware\Components\Theme\Configurator')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFormPersister()
    {
        return $this->getMockBuilder('Shopware\Components\Form\Persister\Theme')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \Shopware\Themes\TestBare\Theme
     */
    protected function getBareTheme()
    {
        require_once(__DIR__ . '/Themes/TestBare/Theme.php');
        return new \Shopware\Themes\TestBare\Theme();
    }

    /**
     * @return \Shopware\Themes\TestResponsive\Theme
     */
    protected function getResponsiveTheme()
    {
        require_once(__DIR__ . '/Themes/TestResponsive/Theme.php');
        return new \Shopware\Themes\TestResponsive\Theme();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTemplate()
    {
        return $this->getMockBuilder('Shopware\Models\Shop\Template')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getShopRepository()
    {
        return $this->getMockBuilder('Shopware\Models\Shop\Repository')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getSnippetHandler()
    {
        return $this->getMockBuilder('Shopware\Components\Snippet\DatabaseHandler')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
