<?php

namespace Shopware\Components\Plugin\XmlReader;

/**
 * Interface XmlReaderInterface
 */
interface XmlReaderInterface
{
    /**
     * @param string $xmlFile
     *
     * @return array
     */
    public function read($xmlFile);
}
