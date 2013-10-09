<?php

namespace Shopware\DependencyInjection\Bridge;

use Zend_Cache_Core;
use Zend_Locale_Data;

/**
 * This is no real factory!
 *
 * Wrapper for accessing the used zend cache instance
 * + call of Zend_Locale_Data::setCache.
 */
class Cache
{
    private $zendCache;

    public function __construct(Zend_Cache_Core $zendCache)
    {
        $this->zendCache = $zendCache;
    }

    public function factory()
    {
        Zend_Locale_Data::setCache($this->zendCache);
        return $this->zendCache;
    }
}
