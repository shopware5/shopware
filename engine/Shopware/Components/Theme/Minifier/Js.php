<?php

namespace Shopware\Components\Theme\Minifier;

class Js
{
    /**
     * @var \JSMin
     */
    private $minifier;

    function __construct($minifier)
    {
        $this->minifier = $minifier;
    }

    public function minify($js)
    {
        return $this->minifier->minify($js);
    }
}