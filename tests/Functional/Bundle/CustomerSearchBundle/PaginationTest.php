<?php

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle;

use Shopware\Bundle\CustomerSearchBundle\Criteria;

class PaginationTest extends TestCase
{
    public function testPagination()
    {
        $criteria = new Criteria();
        $criteria->offset(0);
        $criteria->limit(1);

        $result = $this->search(
            $criteria,
            ['number1'],
            [
                [
                    'email' => 'test1@example.com',
                    'number' => 'number1'
                ],
                [
                    'email' => 'test2@example.com',
                    'number' => 'number2'
                ],
                [
                    'email' => 'test3@example.com',
                    'number' => 'number3'
                ]
            ]
        );

        $this->assertContains('test1@example.com', $result->getEmails());
        $this->assertEquals(3, $result->getTotal());
        $this->assertCount(1, $result->getIds());
        $this->assertCount(1, $result->getRows());
    }
}