<?php

namespace Shopware\Tests\Unit\Components\Plugin\XmlReader;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Plugin\XmlReader\XmlPluginReader;

class XmlPluginReaderTest extends TestCase
{
    /**
     * @var XmlPluginReader
     */
    private $pluginReader;

    /**
     * set up test
     */
    protected function setUp()
    {
        $this->pluginReader = new XmlPluginReader();
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\XmlPluginReader::parseFile()
     * @covers \Shopware\Components\Plugin\XmlReader\XmlPluginReader::parseRequiredPlugins()
     * @covers \Shopware\Components\Plugin\XmlReader\XmlPluginReader::parseBlacklist()
     */
    public function testReadFile()
    {
        $result = $this->readFile('plugin.xml');

        self::assertInternalType('array', $result);

        self::assertArrayHasKey('label', $result);
        self::assertArrayHasKey('en', $result['label']);
        self::assertArrayHasKey('de', $result['label']);
        self::assertEquals('My plugin', $result['label']['de']);
        self::assertEquals('My plugin', $result['label']['en']);

        self::assertArrayHasKey('description', $result);
        self::assertArrayHasKey('en', $result['description']);
        self::assertArrayHasKey('de', $result['description']);
        self::assertEquals("<h2>Mein Plugin</h2>\n<p>Meine Plugin Beschreibugn</p>", $result['description']['de']);
        self::assertEquals("<h2>My Plugin</h2>\n<p>My long description</p>", $result['description']['en']);

        self::assertArrayHasKey('version', $result);
        self::assertArrayHasKey('license', $result);
        self::assertArrayHasKey('author', $result);
        self::assertArrayHasKey('copyright', $result);
        self::assertArrayHasKey('link', $result);

        self::assertEquals('1.5.3', $result['version']);
        self::assertEquals('MIT', $result['license']);
        self::assertEquals('Hasna Corp.', $result['author']);
        self::assertEquals('(c) Hansa Corp.', $result['copyright']);
        self::assertEquals('Some link', $result['link']);

        self::assertArrayHasKey('changelog', $result);
        self::assertArrayHasKey('1.0.6', $result['changelog']);
        self::assertArrayHasKey('1.0.5', $result['changelog']);
        self::assertArrayHasKey('de', $result['changelog']['1.0.6']);
        self::assertArrayHasKey('en', $result['changelog']['1.0.6']);
        self::assertCount(3, $result['changelog']['1.0.6']['de']);
        self::assertCount(1, $result['changelog']['1.0.6']['en']);

        self::assertArrayHasKey('compatibility', $result);
        self::assertArrayHasKey('minVersion', $result['compatibility']);
        self::assertArrayHasKey('maxVersion', $result['compatibility']);
        self::assertArrayHasKey('blacklist', $result['compatibility']);

        self::assertCount(2, $result['compatibility']['blacklist']);

        self::assertArrayHasKey('requiredPlugins', $result);

        self::assertCount(2, $result['requiredPlugins']);

        $firstRequiredPlugin = $result['requiredPlugins'][0];

        self::assertArrayHasKey('minVersion', $firstRequiredPlugin);
        self::assertArrayHasKey('maxVersion', $firstRequiredPlugin);
        self::assertArrayHasKey('blacklist', $firstRequiredPlugin);

        self::assertCount(2, $firstRequiredPlugin['blacklist']);

        self::assertEquals('1.0.2', $firstRequiredPlugin['blacklist'][0]);
        self::assertEquals('1.0.3', $firstRequiredPlugin['blacklist'][1]);

        $secondRequiredPlugin = $result['requiredPlugins'][1];

        self::assertArrayNotHasKey('minVersion', $secondRequiredPlugin);
        self::assertArrayNotHasKey('maxVersion', $secondRequiredPlugin);
        self::assertArrayNotHasKey('blacklist', $secondRequiredPlugin);
    }

    /**
     * helper function to read a plugin xml file
     *
     * @param $file
     *
     * @return array
     */
    private function readFile($file)
    {
        return $this->pluginReader->read(
            sprintf('%s/examples/plugin/%s', __DIR__, $file)
        );
    }
}
