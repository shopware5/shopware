<?php

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle;

use Shopware\Bundle\CustomerSearchBundle\Criteria;

class SearchResultTest extends TestCase
{
    public function testResultCollection()
    {
        $criteria = new Criteria();
        $result = $this->search(
            $criteria,
            ['number1', 'number2', 'number3'],
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
        $this->assertContains('test2@example.com', $result->getEmails());
        $this->assertContains('test3@example.com', $result->getEmails());
        $this->assertEquals(3, $result->getTotal());
        $this->assertCount(3, $result->getIds());
        $this->assertCount(3, $result->getRows());
    }
}