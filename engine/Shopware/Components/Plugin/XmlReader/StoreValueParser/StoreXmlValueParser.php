<?php

namespace Shopware\Components\Plugin\XmlReader\StoreValueParser;

use Shopware\Components\Plugin\XmlReader\XmlReaderBase;

/**
 * Class StoreXmlValueParser
 */
class StoreXmlValueParser implements StoreValueParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse(\DOMElement $element)
    {
        $storeOptions = $element->getElementsByTagName('option');

        if ($storeOptions->length === 0) {
            return [];
        }

        $options = [];

        /** @var \DOMElement $storeOption */
        foreach ($storeOptions as $storeOption) {
            $value = '';
            if ($optionValue = $storeOption->getElementsByTagName('value')->item(0)) {
                $value = $optionValue->nodeValue;
            }

            $label = XmlReaderBase::parseTranslatableNodeList(
                $storeOption->getElementsByTagName('label')
            );

            $options[] = [
                $value,
                $label
            ];
        }

        return $options;
    }
}
