<?php

namespace Shopware\Tests\Unit\Components\Plugin;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Plugin\XmlAttributeReader;

class XmlAttributeReaderReaderTest extends TestCase
{
    /**
     * @var XmlAttributeReader
     */
    private $SUT;

    private $result = [
        's_articles_attributes' =>
            [
                0 =>
                    [
                        'name' => 'test',
                        'type' => 'combobox',
                        'label' => 'I am a checkbox',
                        'supportText' => 'supportText',
                        'helpText' => 'helpText',
                        'translatable' => true,
                        'displayInBackend' => true,
                        'custom' => true,
                        'updateDependingTables' => true,
                        'arrayStore' =>
                            [
                                0 =>
                                    [
                                        'key' => '1',
                                        'value' => 'Yes',
                                    ],
                                1 =>
                                    [
                                        'key' => '0',
                                        'value' => 'No',
                                    ],
                            ],
                        'defaultValue' => NULL,
                    ],
            ],
    ];

    protected function setUp()
    {
        $this->SUT = new XmlAttributeReader();
    }

    public function testCanReadAndVerify()
    {
        $result = $this->SUT->read(__DIR__.'/examples/attributes.xml');
        $this->assertInternalType('array', $result);
    }

    public function testReadCronjob()
    {
        $result = $this->SUT->read(__DIR__.'/examples/attributes.xml');

        $this->assertArraySubset($result, $this->result);
    }
}
