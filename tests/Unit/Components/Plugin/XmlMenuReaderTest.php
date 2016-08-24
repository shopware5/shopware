<?php
namespace Shopware\Tests\Unit\Components\Plugin;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Plugin\XmlMenuReader;

class XmlMenuReaderTest extends TestCase
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

    public function testCanReadMenuWithRootEntry()
    {
        $result = $this->SUT->read(__DIR__.'/examples/menu_root_entry.xml');
        $this->assertInternalType('array', $result);
        $this->assertTrue($result[0]['isRootMenu']);
    }
}
