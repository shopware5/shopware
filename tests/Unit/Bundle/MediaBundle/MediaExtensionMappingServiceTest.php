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

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\MediaBundle\MediaExtensionMappingService;
use Shopware\Bundle\MediaBundle\MediaExtensionMappingServiceInterface;
use Shopware\Models\Media\Media;

class MediaExtensionMappingServiceTest extends TestCase
{
    /**
     * @var MediaExtensionMappingServiceInterface
     */
    private $mappingService;

    protected function setUp(): void
    {
        $this->mappingService = new MediaExtensionMappingService(['xlsx', 'DOCX', 'pdf']);
    }

    public function testAllowedExtensionShouldPass()
    {
        static::assertTrue($this->mappingService->isAllowed('jpg'));
    }

    public function testNotAllowedExtensionShouldFail()
    {
        static::assertFalse($this->mappingService->isAllowed('does_not_exists'));
    }

    public function testGetCorrectTypeForKnownExtension()
    {
        static::assertSame(Media::TYPE_IMAGE, $this->mappingService->getType('jpg'));
    }

    public function testGetCorrectTypeForUnknownExtension()
    {
        static::assertSame(Media::TYPE_UNKNOWN, $this->mappingService->getType('unknown_extension'));
    }

    public function testGetUnknownExtensionForCustomType()
    {
        static::assertSame(Media::TYPE_UNKNOWN, $this->mappingService->getType('xlsx'));
        static::assertSame(Media::TYPE_UNKNOWN, $this->mappingService->getType('docx'));
    }

    public function testCustomFileExtensionsAreAllowed()
    {
        static::assertTrue($this->mappingService->isAllowed('XLSX'));
        static::assertTrue($this->mappingService->isAllowed('docx'));
    }

    public function testExistingExtensionInCustomFileExtensionsIsStillDefined()
    {
        static::assertTrue($this->mappingService->isAllowed('pdf'));
        static::assertSame(Media::TYPE_PDF, $this->mappingService->getType('pdf'));
    }
}
