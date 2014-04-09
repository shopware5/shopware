<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

/**
 * @covers \Shopware\Components\LegacyRequestWrapper\PostWrapper
 */
class Shopware_Tests_Components_LegacyRequestWrapper_PostWrapperTest extends Enlight_Components_Test_Controller_TestCase
{
    private static $resources = array(
        'Admin',
        'Articles',
        'Basket',
        'Categories',
        'cms',
        'Configurator',
        'Core',
        'Export',
        'Marketing',
        'Newsletter',
        'Order',
        'RewriteTable'
    );

    public function setUp()
    {
        parent::setUp();

        $this->dispatch('/');
    }

    public function testSetPost()
    {
        $previousPostData = Shopware()->Front()->Request()->getPost();

        foreach (self::$resources as $name) {
            Shopware()->Front()->Request()->setPost($name, $name.'Value');
        }

        $postData = Shopware()->Front()->Request()->getPost();
        $this->assertNotEquals($previousPostData, $postData);

        foreach (self::$resources as $name) {
            $this->assertEquals($postData, Shopware()->Modules()->getModule($name)->sSYSTEM->_POST->toArray());
        }

        return $postData;
    }

    /**
     * @depends testSetPost
     */
    public function testOverwriteAndClearPost($postData)
    {
        $this->assertNotEquals($postData, Shopware()->Front()->Request()->getPost());

        foreach (self::$resources as $name) {
            Shopware()->Front()->Request()->setPost($postData);
            $this->assertEquals($postData, Shopware()->Modules()->getModule($name)->sSYSTEM->_POST->toArray());
            Shopware()->Modules()->getModule($name)->sSYSTEM->_POST = array();
            $this->assertNotEquals($postData, Shopware()->Modules()->getModule($name)->sSYSTEM->_POST->toArray());
        }

        return $postData;
    }

    public function testGetPost()
    {
        $previousPostData = Shopware()->Front()->Request()->getPost();

        foreach (self::$resources as $name) {
            Shopware()->Modules()->getModule($name)->sSYSTEM->_POST[$name] = $name.'Value';
        }

        $postData = Shopware()->Front()->Request()->getPost();
        $this->assertNotEquals($previousPostData, $postData);

        foreach (self::$resources as $name) {
            $this->assertEquals($postData, Shopware()->Modules()->getModule($name)->sSYSTEM->_POST->toArray());
        }
    }
}
