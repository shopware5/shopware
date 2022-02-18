<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Unit\Bundle\MediaBundle;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Query\QueryBuilder;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Shopware\Bundle\MediaBundle\GarbageCollector;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Bundle\MediaBundle\Struct\MediaPosition;
use Shopware\Tests\TestReflectionHelper;

class GarbageCollectorTest extends TestCase
{
    public function testTablesWithHtmlContent(): void
    {
        $garbageCollector = $this->getGarbageCollector($this->getHtmlTableData());
        $propertyValue = $this->callMethod('handleTablesWithHtmlContent', $garbageCollector)
            ->getValue($garbageCollector);

        static::assertCount(11, $propertyValue['path']);

        static::assertSame([
            'media/image/foo.png',
            'media/image/bar.png',
            'media/pdf/foo.pdf',
            'media/pdf/bar.pdf',
            'media/image/foo2.png',
            'media/image/bar2.png',
            'media/image/foo3.png',
            'media/image/foo4.png',
            'media/image/bar4.png',
            'media/image/foo5.png',
            'media/image/bar5.png',
        ], array_intersect([
            'media/image/foo.png',
            'media/image/bar.png',
            'media/pdf/foo.pdf',
            'media/pdf/bar.pdf',
            'media/image/foo2.png',
            'media/image/bar2.png',
            'media/image/foo3.png',
            'media/image/foo4.png',
            'media/image/bar4.png',
            'media/image/foo5.png',
            'media/image/bar5.png',
        ], $propertyValue['path']));
    }

    public function testHandleTablesWithSerializedContent(): void
    {
        $garbageCollector = $this->getGarbageCollector($this->getSerializedTableData());
        $propertyValue = $this->callMethod('handleTablesWithSerializedContent', $garbageCollector)
            ->getValue($garbageCollector);

        static::assertCount(7, $propertyValue['path']);

        static::assertSame([
            'media/image/Balmoral-Flatcap-Tweed_720x600503f74f066346.jpg',
        ], array_intersect([
            'media/image/Balmoral-Flatcap-Tweed_720x600503f74f066346.jpg',
        ], $propertyValue['path']));
    }

    /**
     * @param array<string> $expectedData
     */
    private function getGarbageCollector(array $expectedData): GarbageCollector
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(static::once())->method('select')->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('from')->willReturn($queryBuilder);

        $result = $this->createMock(Result::class);
        $result->expects(static::once())->method('fetchFirstColumn')->willReturn($expectedData);
        $queryBuilder->expects(static::once())->method('execute')->willReturn($result);

        $connection = $this->createMock(Connection::class);
        $connection->expects(static::once())->method('createQueryBuilder')->willReturn($queryBuilder);

        $mediaService = $this->createMock(MediaServiceInterface::class);
        $mediaService->method('normalize')->willReturnArgument(0);

        return new GarbageCollector(
            [],
            $connection,
            $mediaService
        );
    }

    private function callMethod(string $methodToCall, GarbageCollector $garbageCollector): ReflectionProperty
    {
        $method = TestReflectionHelper::getMethod(GarbageCollector::class, $methodToCall);
        $method->invoke($garbageCollector, $this->getExampleMediaPosition());

        return TestReflectionHelper::getProperty(GarbageCollector::class, 'queue');
    }

    private function getExampleMediaPosition(): MediaPosition
    {
        return new MediaPosition('s_cms_static', 'html', 'path', MediaPosition::PARSE_HTML);
    }

    /**
     * @return array<string>
     */
    private function getHtmlTableData(): array
    {
        return [
            // Media Path
            'Minions ipsum quis consequat belloo! <img src="{media path=\'media/image/foo.png\'}" />Et quis. Bee do bee do bee do dolore adipisicing minim.',
            // Src Tag
            'Bacon ipsum dolor amet t-bone brisket prosciutto <img src="media/image/bar.png" /> biltong rump drumstick pancetta andouille salami jerky beef ribs ball tip.',
            // Href Link Tag
            'Minions ipsum quis consequat belloo! <a href="{media path=\'media/pdf/foo.pdf\'}" >Et quis</a>. <a href="media/pdf/bar.pdf">Bee do</a> bee do bee do dolore adipisicing minim.',
            // Should not match
            'Lorem ipsum dolor sit amet <img src="{file link=\'media/image/foobar.png\'" />',
            'Minions ipsum quis consequat belloo! <a href="{file link=\'media/pdf/foo1.pdf\'}" >Et quis</a>. <a href="https://example.com">Bee do</a> bee do bee do <a href="foo/bar/media/pdf/foobar.pdf">dolore</a> adipisicing minim.',
            // Multiple matches
            'Minions ipsum quis consequat belloo! <img src="{media path=\'media/image/foo2.png\'}" />Et quis. <img src="media/image/bar2.png"/> Bee do bee do bee do dolore adipisicing minim.',
            // data-src match
            'Bacon ipsum dolor amett-bone brisket prosciutto  <img data-src="media/image/foo3.png" />biltong rump drumstick pancetta andouille salami jerky beef ribs ball tip.',

            // Multiple occurrences of media / img
            'Lorem ipsum dolor sit amet, consetetur sadipscing elitr <img src="media/image/foo4.png" /> sed diam nonumy eirmod <img src="media/image/bar4.png" />',
            'Minions ipsum bappleees <img src="{media path=\'media/image/foo5.png\'}" /> aaaaaah bee do bee do bee do hahaha <img src="{media path=\'media/image/bar5.png\'}" />',
        ];
    }

    /**
     * @return array<string>
     */
    private function getSerializedTableData(): array
    {
        return [
            's:19:"2013-12-01 11:15:53";',
            'b:0;',
            'b:0;',
            'i:1645018999;',
            's:32:"Hn1LU61JOe18cA6GsIF3BMv1VKAPVrOW";',
            's:19:"2022-02-16 14:43:21";',
            's:19:"2022-02-15 15:25:09";',
            's:4:"asdf";',
            's:1:"1";',
            'i:1;',
            'i:2;',
            'b:0;',
            'a:1:{i:0;s:2:"AT";}',
            'a:1:{i:0;s:2:"AT";}',
            'b:1;',
            's:59:"media/image/Balmoral-Flatcap-Tweed_720x600503f74f066346.jpg";',
        ];
    }
}
