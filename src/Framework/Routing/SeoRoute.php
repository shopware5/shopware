<?php

namespace Shopware\Framework\Routing;

class SeoRoute
{
    /**
     * @var string
     */
    protected $url;
    
    /**
     * @var string
     */
    protected $seoUrl;

    /**
     * @var string
     */
    protected $name;

    public function __construct(string $name, string $url, string $seoUrl)
    {
        $this->url = $url;
        $this->seoUrl = $seoUrl;
        $this->name = $name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getSeoUrl(): string
    {
        return $this->seoUrl;
    }

    public function getName(): string
    {
        return $this->name;
    }
}