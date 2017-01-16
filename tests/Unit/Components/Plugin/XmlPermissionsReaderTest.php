<?php

namespace Shopware\Tests\Unit\Components\Plugin;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Plugin\XmlPermissionsReader;

/**
 * Class XmlPermissionsReaderTest
 */
class XmlPermissionsReaderTest extends TestCase
{
    /**
     * @var XmlPermissionsReader
     */
    private $reader;

    /**
     * @var array
     */
    private $result = [
        'read',
        'write',
        'blog'
    ];

    protected function setUp()
    {
        $this->reader = new XmlPermissionsReader();
    }

    public function testCanReadAndVerify()
    {
        $result = $this->reader->read(__DIR__ . '/examples/permissions.xml');

        $this->assertInternalType('array', $result);
    }

    public function testResultIsAsExpected()
    {
        $result = $this->reader->read(__DIR__ . '/examples/permissions.xml');

        $this->assertArraySubset($this->result, $result);
    }
}
