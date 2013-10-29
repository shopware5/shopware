<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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

namespace Shopware\Tests\Components\Model;

use Doctrine\ORM\Query;

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class ModelManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Shopware\Components\Model\ModelManager::createPaginator
     */
    public function testCreatePaginator()
    {
        $ref = new \ReflectionClass('Shopware\Components\Model\ModelManager');

        /** @var \Shopware\Components\Model\ModelManager $modelManager */
        $modelManager = $ref->newInstanceWithoutConstructor();

        // Create a stub for the SomeClass class.
        $emMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                       ->disableOriginalConstructor()
                       ->getMock();

        $query = new Query($emMock);

        $paginator = $modelManager->createPaginator($query);

        $this->assertInstanceOf('\Doctrine\ORM\Tools\Pagination\Paginator', $paginator);
        $this->assertSame($query, $paginator->getQuery());
    }

    /**
     * @covers Shopware\Components\Model\ModelManager::getQueryCount
     */
    public function testGetQueryCount()
    {
        $paginator = $this->getMockBuilder('\Doctrine\ORM\Tools\Pagination\Paginator')
                          ->getMock();

        $paginator->expects($this->once())
                ->method('count')
                ->will($this->returnValue(666));

        $manager = $this->getMockBuilder('Shopware\Components\Model\ModelManager')
                ->disableOriginalConstructor()
                ->setMethods(array('createPaginator'))
                ->getMock();

        $query = new \StdClass();
        $manager->expects($this->once())
                ->method('createPaginator')
                ->with($query)
                ->will($this->returnValue($paginator));

        $this->assertEquals(666, $manager->getQueryCount($query));
    }
}
