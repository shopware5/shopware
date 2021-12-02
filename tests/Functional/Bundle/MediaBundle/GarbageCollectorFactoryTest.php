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

namespace Shopware\Tests\Functional\Bundle\MediaBundle;

use Closure;
use Doctrine\DBAL\Connection;
use Enlight_Event_EventManager;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface;
use Shopware\Bundle\AttributeBundle\Service\TypeMappingInterface;
use Shopware\Bundle\MediaBundle\GarbageCollector;
use Shopware\Bundle\MediaBundle\GarbageCollectorFactory;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Bundle\MediaBundle\Struct\MediaPosition;

class GarbageCollectorFactoryTest extends TestCase
{
    public function testTextAttributesAreCollected(): void
    {
        $factory = new GarbageCollectorFactory(
            new Enlight_Event_EventManager(),
            Shopware()->Container()->get(Connection::class),
            Shopware()->Container()->get(MediaServiceInterface::class)
        );
        $collector = $factory->factory();

        $currentCount = \count($this->getMediaPositionsFromGarbageCollector($collector));

        Shopware()->Container()->get(CrudServiceInterface::class)->update('s_articles_attributes', 'foo', TypeMappingInterface::TYPE_HTML);

        $collector = $factory->factory();

        static::assertNotCount($currentCount, $this->getMediaPositionsFromGarbageCollector($collector));

        Shopware()->Container()->get(CrudServiceInterface::class)->delete('s_articles_attributes', 'foo');
    }

    /**
     * @return MediaPosition[]
     */
    private function getMediaPositionsFromGarbageCollector(GarbageCollector $collector): array
    {
        $getMediaPositions = static function (GarbageCollector $collector) {
            return $collector->mediaPositions;
        };
        $getMediaPositions = Closure::bind($getMediaPositions, null, $collector);

        return $getMediaPositions($collector);
    }
}
