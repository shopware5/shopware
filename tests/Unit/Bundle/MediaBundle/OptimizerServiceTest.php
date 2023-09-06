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
use RuntimeException;
use Shopware\Bundle\MediaBundle\Exception\OptimizerNotFoundException;
use Shopware\Bundle\MediaBundle\Optimizer\OptimizerInterface;
use Shopware\Bundle\MediaBundle\OptimizerService;

class OptimizerServiceTest extends TestCase
{
    /**
     * @var OptimizerService
     */
    private $optimizerService;

    /**
     * @var ArrayCollection
     */
    private $optimizers = [];

    protected function setUp(): void
    {
        $this->optimizers = new ArrayCollection([
            new RunnableUnitOptimizer(),
            new NotRunnableUnitOptimizer(),
            new SingleRunnableUnitOptimizer(),
        ]);

        $this->optimizerService = new OptimizerService($this->optimizers);
    }

    public function testOptimizeWithNoOptimizers()
    {
        $this->expectException(OptimizerNotFoundException::class);
        $file = __DIR__ . '/fixtures/sw-icon.png';

        $optimizerService = new OptimizerService(new ArrayCollection());
        $optimizerService->optimize($file);
    }

    public function testFindOptimizerByMimeTypeWithEmptyInput()
    {
        $this->expectException(OptimizerNotFoundException::class);
        $this->optimizerService->getOptimizerByMimeType(null);
    }

    public function testFindOptimizerByMimeTypeWithUnknownMimeType()
    {
        $this->expectException(OptimizerNotFoundException::class);
        $this->optimizerService->getOptimizerByMimeType('image/jpeg');
    }

    public function testFindOptimizerByMimeTypeWithMultipleMatchingOptimizer()
    {
        $optimizer = $this->optimizerService->getOptimizerByMimeType('application/unit-test');
        static::assertInstanceOf(RunnableUnitOptimizer::class, $optimizer);
    }

    public function testFindOptimizerByMimeTypeWithSingleMatchingOptimizer()
    {
        $optimizer = $this->optimizerService->getOptimizerByMimeType('application/single-runnable');
        static::assertInstanceOf(SingleRunnableUnitOptimizer::class, $optimizer);
    }

    public function testFindOptimizerByMimeTypeWithSingleMatchingButNotRunnableOptimizer()
    {
        $this->expectException(OptimizerNotFoundException::class);
        $this->optimizerService->getOptimizerByMimeType('application/not-runnable');
    }

    public function testGetOptimizers()
    {
        $optimizers = $this->optimizerService->getOptimizers();
        static::assertIsArray($optimizers);
        static::assertSame($this->optimizers->toArray(), $optimizers);
    }

    public function testOptimize()
    {
        $file = __DIR__ . '/fixtures/sw-icon.png';

        $this->optimizerService->optimize($file);

        $optimizer = $this->optimizerService->getOptimizerByMimeType('image/png');
        static::assertEquals(1, $optimizer->runCount);
    }
}

abstract class UnitOptimizer implements OptimizerInterface
{
    /**
     * @var int
     */
    public $callCount = 0;

    /**
     * @var int
     */
    public $runCount = 0;

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
        ++$this->runCount;
    }

    /**
     * @return array
     */
    public function getSupportedMimeTypes()
    {
        throw new RuntimeException('This method should be overwritten.');
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

class RunnableUnitOptimizer extends UnitOptimizer
{
    /**
     * @return array
     */
    public function getSupportedMimeTypes()
    {
        return ['application/unit-test', 'application/test-unit', 'image/png'];
    }
}

class NotRunnableUnitOptimizer extends UnitOptimizer
{
    /**
     * @return array
     */
    public function getSupportedMimeTypes()
    {
        return ['application/unit-test', 'application/test-unit', 'application/not-runnable'];
    }

    /**
     * @return bool
     */
    public function isRunnable()
    {
        ++$this->callCount;

        return false;
    }
}

class SingleRunnableUnitOptimizer extends UnitOptimizer
{
    /**
     * @return array
     */
    public function getSupportedMimeTypes()
    {
        return ['application/single-runnable'];
    }
}
