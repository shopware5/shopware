<?php

namespace Shopware\tests\Unit\Components\Template;

require __DIR__ . '/../../../../engine/Library/Enlight/Template/Plugins/modifier.tax.php';

use PHPUnit\Framework\TestCase;

class SmartyTaxModifierTest extends TestCase
{
    /**
     * @param mixed $tax
     * @param int|float $expected
     * @param string $locale
     * @dataProvider taxProvider
     */
    public function testTaxModifierWithValid($tax, $expected, $locale)
    {
        $this->assertSame(
            $expected,
            smarty_modifier_tax($tax, $locale)
        );
    }

    /**
     * @param mixed $tax
     * @param string $locale
     * @dataProvider taxProviderInvalid
     */
    public function testTaxModifierWithInvalid($tax, $locale)
    {
        $this->expectException(\InvalidArgumentException::class);
        smarty_modifier_tax($tax, $locale);
    }

    /**
     * @return array
     */
    public function taxProvider()
    {
        return [
            ['19.5', '19,50', 'de'],
            ['7', '7', 'de'],
            ['6.67', '6,67', 'de'],
            ['21.56', '21.56', 'en'],
            ['9', '9', 'en'],
            ['19.00', '19', 'de'],
            [19.00, '19', 'de'],
            [19, '19', 'en'],
            [19.5, '19,50', 'de'],
            [9999, '9.999', 'de'],
            [9999, '9,999', 'en'],
            [9999.99, '9.999,99', 'de'],
            [9999.99, '9,999.99', 'en']
        ];
    }

    /**
     * @return array
     */
    public function taxProviderInvalid()
    {
        return [
            ['true', 'en'],
            ['false', 'en'],
            ['null', 'en'],
            [true, 'en'],
            [false,  'en'],
            [null, 'en'],
            ['', 'en']
        ];
    }
}
