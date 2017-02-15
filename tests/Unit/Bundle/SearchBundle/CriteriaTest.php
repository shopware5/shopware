<?php

namespace Shopware\Tests\Unit\Bundle\SearchBundle;

use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\SearchBundle\Criteria;

class CriteriaTest extends TestCase
{
    /**
     * @dataProvider invalidCriteriaLimit
     * @expectedException InvalidArgumentException
     * @param $limit
     */
    public function testInvalidCriteriaLimit($limit)
    {
        $criteria = new Criteria();
        $criteria->limit($limit);
    }

    /**
     * @dataProvider validCriteriaLimit
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
     * @expectedException InvalidArgumentException
     * @param $offset
     */
    public function testInvalidCriteriaOffset($offset)
    {
        $criteria = new Criteria();
        $criteria->offset($offset);
    }

    /**
     * @dataProvider validCriteriaOffset
     * @param $offset
     */
    public function testValidCriteriaOffset($offset)
    {
        $criteria = new Criteria();
        $criteria->offset($offset);
        $this->assertEquals($offset, $criteria->getOffset());
    }

    public function validCriteriaLimit()
    {
        return [
            [1],
            [null],
            [200]
        ];
    }

    public function validCriteriaOffset()
    {
        return [
            [0],
            [1],
            [20]
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
