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

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Models_Category_PathByIdTest extends Enlight_Components_Test_TestCase
{
    /**
     * @var \Shopware\Models\Category\Repository
     */
    protected $repo = null;

    /**
     * @return Shopware\Models\Category\Repository
     */
    protected function getRepo()
    {
        if ($this->repo === null) {
            $this->repo = Shopware()->Models()->Category();
        }

        return $this->repo;
    }

    public function simpleNameArrayProvider()
    {
        return array(
            array(1, array(1 => 'Root')),
            array(3, array(3 => 'Deutsch')),
            array(39, array(39 => 'English')),
            array(6, array(3 => 'Deutsch', 6 => 'Sommerwelten')),
            array(11, array(3 => 'Deutsch', 5 => 'Genusswelten', 11 => 'Tees und Zubehör')),
            array(48, array(39 => 'English', 43 => 'Worlds of indulgence', 47 => 'Teas and Accessories', 48 => 'Teas')),
        );
    }

    public function simpleIdArrayProvider()
    {
        return array(
            array(1, array(1 => 1)),
            array(3, array(3 => 3)),
            array(39, array(39 => 39)),
            array(6, array(3 => 3, 6 => 6)),
            array(11, array(3 => 3, 5 => 5, 11 => 11)),
            array(48, array(39 => 39, 43 => 43, 47 => 47, 48 => 48)),
        );
    }

    public function multiArrayProvider()
    {
        return array(
            array(1, array(
                1 => array('id' => 1, 'name' => 'Root', 'blog' => false)
            )),
            array(3, array(
                3 => array('id' => 3, 'name' => 'Deutsch', 'blog' => false)
            )),
            array(39, array(
                39 => array('id' => 39, 'name' => 'English', 'blog' => false)
            )),
            array(5, array(
                3 => array('id' => 3, 'name' => 'Deutsch', 'blog' => false),
                5 => array('id' => 5, 'name' => 'Genusswelten', 'blog' => false),
            )),
            array(48, array(
                39 => array('id' => 39, 'name' => 'English', 'blog' => false),
                43 => array('id' => 43, 'name' => 'Worlds of indulgence', 'blog' => false),
                47 => array('id' => 47, 'name' => 'Teas and Accessories', 'blog' => false),
                48 => array('id' => 48, 'name' => 'Teas', 'blog' => false),
            )),
        );
    }

    public function stringPathProvider()
    {
        return array(
            array(1, 'Root'),
            array(3, 'Deutsch'),
            array(39, 'English'),
            array(5, 'Deutsch > Genusswelten'),
            array(12, 'Deutsch > Genusswelten > Tees und Zubehör > Tees'),
            array(48, 'English > Worlds of indulgence > Teas and Accessories > Teas'),
        );
    }

    /**
     * @dataProvider simpleNameArrayProvider
     */
    public function testGetPathByIdWithDefaultParameters($categoryId, $expectedResult)
    {
        $result = $this->getRepo()->getPathById($categoryId);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider simpleNameArrayProvider
     */
    public function testGetPathByIdWithDefaultNameParameter($categoryId, $expectedResult)
    {
        $result = $this->getRepo()->getPathById($categoryId, 'name');
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider simpleIdArrayProvider
     */
    public function testGetPathByIdWithIdParameter($categoryId, $expectedResult)
    {
        $result = $this->getRepo()->getPathById($categoryId, 'id');
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider multiArrayProvider
     */
    public function testGetPathByIdShouldReturnArray($categoryId, $expectedResult)
    {
        $result = $this->getRepo()->getPathById($categoryId, array('id', 'name', 'blog'));
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider stringPathProvider
     */
    public function testGetPathByIdShouldReturnPathAsString($categoryId, $expectedResult)
    {
        $result = $this->getRepo()->getPathById($categoryId, 'name', ' > ');
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider stringPathProvider
     */
    public function testGetPathByIdShouldReturnPathAsStringWithCustomSeparator($categoryId, $expectedResult)
    {
        $expectedResult = str_replace(' > ', '|', $expectedResult);

        $result = $this->getRepo()->getPathById($categoryId, 'name', '|');
        $this->assertEquals($expectedResult, $result);
    }
}
