<?php

use Shopware\Components\Plugin\XmlMenuReader;

class XmlMenuReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var XmlMenuReader
     */
    private $SUT;

    protected function setUp()
    {
        $this->SUT = new XmlMenuReader();
    }

    public function testCanReadAndVerifyMinimal()
    {
        $result = $this->SUT->read(__DIR__.'/examples/menu_minimal.xml');
        $this->assertInternalType('array', $result);
    }

    public function testCanReadAndVerify()
    {
        $result = $this->SUT->read(__DIR__.'/examples/menu.xml');
        $this->assertInternalType('array', $result);
    }
}
