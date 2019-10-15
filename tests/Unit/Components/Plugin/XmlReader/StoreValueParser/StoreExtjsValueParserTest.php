<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Unit\Components\Plugin\XmlReader\StoreValueParser;

use DOMDocument;
use DOMElement;
use DOMXPath;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Plugin\XmlReader\StoreValueParser\StoreValueParserFactory;
use Shopware\Components\Plugin\XmlReader\StoreValueParser\StoreXmlValueParser;
use Symfony\Component\Config\Util\XmlUtils;

class StoreExtjsValueParserTest extends TestCase
{
    /**
     * @var StoreXmlValueParser
     */
    private $parser;

    /**
     * @var DOMDocument
     */
    private $xmlFile;

    /**
     * @var DOMXPath
     */
    private $xpath;

    protected function setUp(): void
    {
        $this->parser = StoreValueParserFactory::create('extjs');

        $this->xmlFile = XmlUtils::loadFile(
            __DIR__ . '/../examples/config/config_store_extjs.xml',
            __DIR__ . '/../../../../../../engine/Shopware/Components/Plugin/schema/config.xsd'
        );

        $this->xpath = new DOMXPath($this->xmlFile);
    }

    public function testThatParserReturnsValidData(): void
    {
        $store = $this->getStoreElement(1);
        $options = $this->parser->parse($store);

        static::assertIsString($options);
        static::assertEquals('Shopware.apps.Base.store.Category', $options);
    }

    public function testThatEmptyOptionsReturnsEmptyArray(): void
    {
        $store = $this->getStoreElement(2);
        $options = $this->parser->parse($store);

        static::assertIsString($options);
        static::assertEquals('', $options);
    }

    private function getStoreElement(int $elementIndex): DOMElement
    {
        $stores = $this->xpath->query(
            sprintf(
                '//config/elements/element[%s]/store',
                $elementIndex
            )
        );

        return $stores->item(0);
    }
}
