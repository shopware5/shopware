<?php
use Shopware\Components\Plugin\XmlPluginInfoReader;

class XmlPluginInfoReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var XmlPluginInfoReader
     */
    private $SUT;

    protected function setUp()
    {
        $this->SUT = new XmlPluginInfoReader();
    }

    public function testCanReadAndVerifyMinimalExample()
    {
        $result = $this->SUT->read(__DIR__.'/examples/plugin_minimal.xml');
        $this->assertInternalType('array', $result);
    }

    public function testCanReadAndVerify()
    {
        $result = $this->SUT->read(__DIR__.'/examples/plugin.xml');
        $this->assertInternalType('array', $result);
    }
}
