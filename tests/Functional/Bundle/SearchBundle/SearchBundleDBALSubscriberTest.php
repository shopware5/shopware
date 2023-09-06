<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Bundle\SearchBundle;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\CriteriaRequestHandler\CoreCriteriaRequestHandler;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandler\CategoryConditionHandler;
use Shopware\Bundle\SearchBundleDBAL\SearchBundleDBALSubscriber;
use Shopware\Bundle\SearchBundleDBAL\SortingHandler\PopularitySortingHandler;
use Shopware\Bundle\SearchBundleDBAL\SortingHandler\ProductNameSortingHandler;

class SearchBundleDBALSubscriberTest extends TestCase
{
    public function testValidCreate(): void
    {
        $criteriaRequestHandler = $this->createMock(CoreCriteriaRequestHandler::class);

        $subscriber = new SearchBundleDBALSubscriber([
            new CategoryConditionHandler(),
            new PopularitySortingHandler(),
            $criteriaRequestHandler,
        ]);

        $conditionHandlers = $subscriber->registerConditionHandlers();
        static::assertInstanceOf(ArrayCollection::class, $conditionHandlers);
        $facetHandlers = $subscriber->registerFacetHandlers();
        static::assertInstanceOf(ArrayCollection::class, $facetHandlers);
        $requestHandlers = $subscriber->registerRequestHandlers();
        static::assertInstanceOf(ArrayCollection::class, $requestHandlers);
        $sortingHandlers = $subscriber->registerSortingHandlers();
        static::assertInstanceOf(ArrayCollection::class, $sortingHandlers);
    }

    public function testNestedArrays(): void
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Unknown handler class array detected');
        new SearchBundleDBALSubscriber([
            [new CategoryConditionHandler(), new CategoryConditionHandler()],
            new PopularitySortingHandler(),
            new ProductNameSortingHandler(),
        ]);
    }

    public function testEmptyArray(): void
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('No handlers provided in Shopware\Bundle\SearchBundleDBAL\SearchBundleDBALSubscriber');
        new SearchBundleDBALSubscriber([]);
    }

    public function testInvalidClass(): void
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Unknown handler class Shopware\Bundle\SearchBundle\Condition\CategoryCondition detected');
        new SearchBundleDBALSubscriber([
            new CategoryCondition([1, 2]),
            new CategoryConditionHandler(),
        ]);
    }
}
