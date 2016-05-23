<?php

use Shopware\Components\Plugin\XmlConfigDefinitionReader;

class XmlConfigDefinitionReaderTest extends \PHPUnit_Framework_TestCase
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
}
