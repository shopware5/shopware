<?php
namespace Shopware\Tests\Unit\Components\Plugin;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Plugin\XmlConfigDefinitionReader;

class XmlConfigDefinitionReaderTest extends TestCase
{
    /**
     * @var XmlConfigDefinitionReader
     */
    private $SUT;

    protected function setUp()
    {
        $this->SUT = new XmlConfigDefinitionReader();
    }

    public function testCanReadAndVerifyMinimalExample()
    {
        $result = $this->SUT->read(__DIR__.'/examples/config_minimal.xml');
        $this->assertInternalType('array', $result);
    }

    public function testCanReadAndVerify()
    {
        $result = $this->SUT->read(__DIR__.'/examples/config.xml');
        $this->assertInternalType('array', $result);
    }

    public function testCanReadStores()
    {
        $form = $this->SUT->read(__DIR__.'/examples/config_store.xml');
        $this->assertInternalType('array', $form);

        $expected = [
            ['1', 'Test 1'],
            ['2', 'Test 2'],
            ['3', 'Test 3'],
        ];

        $this->assertEquals($expected, $form['elements'][0]['store']);
        $this->assertEquals('Shopware.apps.Base.store.Category', $form['elements'][1]['store']);
    }
}
