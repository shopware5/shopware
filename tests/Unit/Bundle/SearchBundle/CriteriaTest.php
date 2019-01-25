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

namespace Shopware\Tests\Unit\Bundle\SearchBundle;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaRequestHandler\CoreCriteriaRequestHandler;

class CriteriaTest extends TestCase
{
    /**
     * @dataProvider invalidCriteriaLimit
     * @expectedException \InvalidArgumentException
     *
     * @param $limit
     */
    public function testInvalidCriteriaLimit($limit)
    {
        $criteria = new Criteria();
        $criteria->limit($limit);
    }

    /**
     * @dataProvider validCriteriaLimit
     *
     * @param $limit
     */
    public function testValidCriteriaLimit($limit)
    {
        $criteria = new Criteria();
        $criteria->limit($limit);
        $this->assertEquals($criteria->getLimit(), $limit);
    }

    /**
     * @dataProvider invalidCriteriaOffset
     * @expectedException \InvalidArgumentException
     *
     * @param $offset
     */
    public function testInvalidCriteriaOffset($offset)
    {
        $criteria = new Criteria();
        $criteria->offset($offset);
    }

    /**
     * @dataProvider validCriteriaOffset
     *
     * @param $offset
     */
    public function testValidCriteriaOffset($offset)
    {
        $criteria = new Criteria();
        $criteria->offset($offset);
        $this->assertEquals($offset, $criteria->getOffset());
    }

    public function testAdditionalCategoryFilter()
    {
        $configMock = $this->createMock(\Shopware_Components_Config::class);
        $searchTermProcessorMock = $this->createMock(\Shopware\Bundle\SearchBundle\SearchTermPreProcessor::class);
        $contextMock = $this->createMock(\Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface::class);
        $cusomterGroupMock = $this->createMock(\Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group::class);

        $cusomterGroupMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $contextMock->expects($this->any())
            ->method('getCurrentCustomerGroup')
            ->will($this->returnValue($cusomterGroupMock));

        $requestHandler = new CoreCriteriaRequestHandler($configMock, $searchTermProcessorMock);
        $criteria = new Criteria();

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setParams([
            'sCategory' => '1',
            'categoryFilter' => '2',
        ]);
        $requestHandler->handleRequest($request, $criteria, $contextMock);

        $this->assertEquals(1, $criteria->getBaseCondition('category')->getCategoryIds()[0]);
        $this->assertEquals(2, $criteria->getUserCondition('category')->getCategoryIds()[0]);
    }

    public function validCriteriaLimit()
    {
        return [
            [1],
            [null],
            [200],
        ];
    }

    public function validCriteriaOffset()
    {
        return [
            [0],
            [1],
            [20],
        ];
    }

    public function invalidCriteriaOffset()
    {
        return [
            [-1],
            ['123-2'],
            ['asfkln'],
            [null],
            [new \DateTime()],
        ];
    }

    public function invalidCriteriaLimit()
    {
        return [
            [0],
            [-1],
            ['123-2'],
            ['asfkln'],
            [new \DateTime()],
        ];
    }
}
