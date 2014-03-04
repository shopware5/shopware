<?php

namespace Shopware\Components\Theme\Minifier;

class Css
{
    /**
     * @var \CSSmin
     */
    private $minifier;

    function __construct($minifier)
    {
        $this->minifier = $minifier;
    }

    public function minify($css)
    {
        return $this->minifier->run($css);
    }
}