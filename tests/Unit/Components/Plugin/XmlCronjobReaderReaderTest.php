<?php

namespace Shopware\Tests\Unit\Components\Plugin;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Plugin\XmlCronjobReader;

class XmlCronjobReaderReaderTest extends TestCase
{
    /**
     * @var XmlCronjobReader
     */
    private $SUT;

    private $result = [
        'name' => 'Article Importer',
        'action' => 'ImportArticle',
        'active' => true,
        'interval' => 3600,
        'disable_on_error' => false
    ];

    protected function setUp()
    {
        $this->SUT = new XmlCronjobReader();
    }

    public function testCanReadAndVerify()
    {
        $result = $this->SUT->read(__DIR__.'/examples/cronjob.xml');
        $this->assertInternalType('array', $result);
    }

    public function testReadCronjob()
    {
        $result = $this->SUT->read(__DIR__.'/examples/cronjob.xml');

        $this->assertArraySubset(current($result), $this->result);
    }
}
