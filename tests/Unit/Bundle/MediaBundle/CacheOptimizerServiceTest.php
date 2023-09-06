<?php
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

namespace Shopware\Tests\Unit\Bundle\MediaBundle;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\MediaBundle\CacheOptimizerService;
use Shopware\Bundle\MediaBundle\Optimizer\OptimizerInterface;
use Shopware\Bundle\MediaBundle\OptimizerService;
use Shopware\Bundle\MediaBundle\OptimizerServiceInterface;

class CacheOptimizerServiceTest extends TestCase
{
    /**
     * @var CacheOptimizerService
     */
    private $optimizerService;

    protected function setUp(): void
    {
        $this->optimizerService = new CacheOptimizerService(
            new OptimizerService(new ArrayCollection([
                new TestPngOptimizer(),
            ]))
        );
    }

    public function testFindOptimizerByMimeTypeToCacheTheMapping()
    {
        // first call should delegate to decorated service
        $this->optimizerService->getOptimizerByMimeType('image/png');

        // second call should hit cached optimizer mappings
        $optimizer = $this->optimizerService->getOptimizerByMimeType('image/png');

        static::assertEquals(1, $optimizer->callCount);
    }

    public function testOptimizeDecoration()
    {
        $decoratedService = $this->createMock(OptimizerServiceInterface::class);
        $decoratedService->expects(static::once())->method('optimize');

        $cacheService = new CacheOptimizerService($decoratedService);
        $cacheService->optimize('file');
    }

    public function testGetOptimizersDecoration()
    {
        $decoratedService = $this->createMock(OptimizerServiceInterface::class);
        $decoratedService->expects(static::once())->method('getOptimizers');

        $cacheService = new CacheOptimizerService($decoratedService);
        $cacheService->getOptimizers();
    }
}

class TestPngOptimizer implements OptimizerInterface
{
    /**
     * @var int
     */
    public $callCount = 0;

    /**
     * @return string
     */
    public function getName()
    {
        return 'UnitOptimizer';
    }

    /**
     * @param string $filepath
     */
    public function run($filepath)
    {
    }

    /**
     * @return array
     */
    public function getSupportedMimeTypes()
    {
        return ['image/png'];
    }

    /**
     * @return bool
     */
    public function isRunnable()
    {
        ++$this->callCount;

        return true;
    }
}
