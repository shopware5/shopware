<?php

namespace Shopware\Components\Theme\Compressor;

/**
 * Interface CompressorInterface
 *
 * @package Shopware\Components\Theme\Compressor
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