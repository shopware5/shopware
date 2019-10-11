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

namespace Shopware\Tests\Unit\Bundle\MediaBundle;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\MediaBundle\GarbageCollector;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Bundle\MediaBundle\Struct\MediaPosition;

class GarbageCollectorTest extends TestCase
{
    public function testHandleHtmlTable()
    {
        $garbageCollector = $this->getGarbageCollector();
        $queueProperty = $this->callHandleHtmlTable($garbageCollector);

        $propertyValue = $queueProperty->getValue($garbageCollector);

        static::assertCount(11, $propertyValue['path']);

        static::assertTrue(array_intersect([
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
        ], $propertyValue['path']) === [
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
        ]);
    }

    private function getGarbageCollector()
    {
        return new GarbageCollector(
            [],
            new DbalConnectionMock(),
            new MediaServiceMock()
        );
    }

    private function callHandleHtmlTable(GarbageCollector $garbageCollector)
    {
        $refl = new \ReflectionClass($garbageCollector);
        $method = $refl->getMethod('handleHtmlTable');
        $method->setAccessible(true);

        $method->invoke($garbageCollector, $this->getExampleMediaPosition());

        $propertyRefl = $refl->getProperty('queue');
        $propertyRefl->setAccessible(true);

        return $propertyRefl;
    }

    private function getExampleMediaPosition()
    {
        return new MediaPosition('s_cms_static', 'html', 'path', MediaPosition::PARSE_HTML);
    }
}

class DbalConnectionMock extends Connection
{
    public function __construct()
    {
    }

    public function createQueryBuilder()
    {
        return new QueryBuilderMock();
    }
}

class MediaServiceMock extends MediaService
{
    public function __construct()
    {
    }

    public function normalize($path)
    {
        return $path;
    }
}

class QueryBuilderMock
{
    public function __call($name, $arguments)
    {
        if ($name === 'fetchAll') {
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

        return $this;
    }
}
