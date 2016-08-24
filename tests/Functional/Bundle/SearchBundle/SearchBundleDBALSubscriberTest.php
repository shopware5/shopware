<?php

namespace Shopware\Tests\Functional\Bundle\SearchBundle;

use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\CriteriaRequestHandler\CoreCriteriaRequestHandler;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandler\CategoryConditionHandler;
use Shopware\Bundle\SearchBundleDBAL\SearchBundleDBALSubscriber;
use Shopware\Bundle\SearchBundleDBAL\SortingHandler\PopularitySortingHandler;
use Shopware\Bundle\SearchBundleDBAL\SortingHandler\ProductNameSortingHandler;

class SearchBundleDBALSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testValidCreate()
    {
        $criteriaRequestHandler = $this->createMock(CoreCriteriaRequestHandler::class);

        $subscriber = new SearchBundleDBALSubscriber([
            new CategoryConditionHandler(),
            new PopularitySortingHandler(),
            $criteriaRequestHandler,
        ]);

        $this->assertInstanceOf('\Shopware\Bundle\SearchBundleDBAL\SearchBundleDBALSubscriber', $subscriber);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unknown handler class  detected
     */
    public function testNestedArrays()
    {
        new SearchBundleDBALSubscriber([
            [new CategoryConditionHandler(), new CategoryConditionHandler()],
            new PopularitySortingHandler(),
            new ProductNameSortingHandler()
        ]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No handlers provided in \Shopware\Bundle\SearchBundleDBAL\SearchBundleDBALSubscriber
     */
    public function testEmptyArray()
    {
        new SearchBundleDBALSubscriber([]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unknown handler class Shopware\Bundle\SearchBundle\Condition\CategoryCondition detected
     */
    public function testInvalidClass()
    {
        new SearchBundleDBALSubscriber([
            new CategoryCondition([1, 2]),
            new CategoryConditionHandler()
        ]);
    }
}
