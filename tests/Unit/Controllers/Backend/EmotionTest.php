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

namespace Shopware\Tests\Unit\Controllers\Backend;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\ContainerAwareEventManager;
use Shopware\Models\Emotion\Library\Field;
use Shopware\Tests\TestReflectionHelper;
use Shopware_Controllers_Backend_Emotion;

class EmotionTest extends TestCase
{
    public function testProcessDataFieldValue(): void
    {
        $processDataFieldValueMethod = TestReflectionHelper::getMethod(Shopware_Controllers_Backend_Emotion::class, 'processDataFieldValue');

        $mediaServiceMock = $this->createMock(MediaServiceInterface::class);
        $mediaServiceMock->expects(static::exactly(2))->method('normalize')->willReturn('path/to/media.jpg');

        $eventManagerMock = $this->createMock(ContainerAwareEventManager::class);
        $eventManagerMock->expects(static::once())->method('collect')->willReturn(new ArrayCollection());
        $controller = new Shopware_Controllers_Backend_Emotion($mediaServiceMock, $eventManagerMock);

        $field = new Field();
        $field->setValueType(Field::VALUE_TYPE_JSON);
        $initialValue = [
                [
                    'position' => 1,
                    'path' => 'http://localhost/path/to/media.jpg',
                    'mediaId' => 1,
                    'link' => '',
                ],
                [
                    'position' => 2,
                    'mediaId' => 2,
                    'link' => '',
                ],
                [
                    'position' => 3,
                    'path' => 'http://localhost/path/to/media.jpg',
                    'mediaId' => 3,
                    'link' => '',
                ],
        ];

        $processedValue = $processDataFieldValueMethod->invoke($controller, $field, $initialValue);

        static::assertSame('[{"position":1,"path":"path\/to\/media.jpg","mediaId":1,"link":""},{"position":2,"mediaId":2,"link":""},{"position":3,"path":"path\/to\/media.jpg","mediaId":3,"link":""}]', $processedValue);
    }

    public function testProcessDataFieldValueWithNullValue(): void
    {
        $processDataFieldValueMethod = TestReflectionHelper::getMethod(Shopware_Controllers_Backend_Emotion::class, 'processDataFieldValue');

        $mediaServiceMock = $this->createMock(MediaServiceInterface::class);
        $eventManagerMock = $this->createMock(ContainerAwareEventManager::class);
        $eventManagerMock->expects(static::once())->method('collect')->willReturn(new ArrayCollection());
        $controller = new Shopware_Controllers_Backend_Emotion($mediaServiceMock, $eventManagerMock);

        $field = new Field();
        $initialValue = null;

        $processedValue = $processDataFieldValueMethod->invoke($controller, $field, $initialValue);

        static::assertNull($processedValue);
    }
}
