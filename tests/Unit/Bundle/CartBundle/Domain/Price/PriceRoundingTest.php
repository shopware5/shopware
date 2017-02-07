<?php

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\Price;

use Shopware\Bundle\CartBundle\Domain\Price\PriceRounding;

class PriceRoundingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getCases
     * @param $price
     * @param $expected
     * @param $precision
     */
    public function testWithValidNumbers($price, $expected, $precision)
    {
        $rounding = new PriceRounding($precision);
        static::assertEquals($expected, $rounding->round($price));
    }

    /**
     * @dataProvider invalidPrices
     * @param mixed $price
     * @expectedException \RuntimeException
     */
    public function testWithNoneValidNumbers($price)
    {
        $rounding = new PriceRounding(2);
        $rounding->round($price);
    }

    public function invalidPrices()
    {
        return [
            [false],
            [true],
            ['invalid']
        ];
    }

    public function getCases()
    {
        return [
            [0, 0, 0],
            [0, 0, 1],
            [0, 0, 2],

            [1, 1, 0],
            [1, 1, 1],
            [1, 1, 2],
            [1, 1, 3],

            [1.1, 1, 0],
            [1.1, 1.1, 1],
            [1.1, 1.1, 2],
            [1.1, 1.1, 3],

            [1.4444, 1, 0],
            [1.4444, 1.4, 1],
            [1.4444, 1.44, 2],
            [1.4444, 1.444, 3],

            [0.55555, 1, 0],
            [0.55555, 0.6, 1],
            [0.55555, 0.56, 2],
            [0.55555, 0.556, 3],

            [-1.4444, -1, 0],
            [-1.4444, -1.4, 1],
            [-1.4444, -1.44, 2],
            [-1.4444, -1.444, 3],

            [-1.55555, -2, 0],
            [-1.55555, -1.6, 1],
            [-1.55555, -1.56, 2],
            [-1.55555, -1.556, 3],
            ['-1.55555', -1.556, 3]
        ];
    }
}
