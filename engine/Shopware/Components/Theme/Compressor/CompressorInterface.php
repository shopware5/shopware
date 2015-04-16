<?php

namespace Shopware\Components\Theme\Compressor;

/**
 * Interface CompressorInterface
 *
 * @category  Shopware
 * @package   Shopware\Components\Theme\Compressor
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
interface CompressorInterface
{
    /**
     * Compress the passed content and returns
     * the compressed content.
     *
     * @param string $content
     * @return string
     */
    public function compress($content);
}
