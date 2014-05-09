<?php

namespace Shopware\Struct;

use Shopware\Struct\Property\Set;

class Product extends ListProduct
{
    /**
     * @var ListProduct[]
     */
    private $related;

    /**
     * @var ListProduct[]
     */
    private $similar;

    /**
     * @var Product\Download[]
     */
    private $downloads;

    /**
     * @var Product\Link[]
     */
    private $links;

    /**
     * @var Media[]
     */
    private $media;

    /**
     * @var Product\Vote[]
     */
    private $votes;

    /**
     * @var Set
     */
    private $propertySet;

    /**
     * @var Product\Configurator\Set
     */
    private $configurator;

    /**
     * @var Product\Configurator\Option[]
     */
    private $configuration;

    /**
     * @param \Shopware\Struct\Media[] $media
     */
    public function setMedia($media)
    {
        $this->media = $media;
    }

    /**
     * @return \Shopware\Struct\Media[]
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param \Shopware\Struct\Property\Set $propertySet
     */
    public function setPropertySet($propertySet)
    {
        $this->propertySet = $propertySet;
    }

    /**
     * @return \Shopware\Struct\Property\Set
     */
    public function getPropertySet()
    {
        return $this->propertySet;
    }

    /**
     * @param \Shopware\Struct\Product\Vote[] $votes
     */
    public function setVotes($votes)
    {
        $this->votes = $votes;
    }

    /**
     * @return \Shopware\Struct\Product\Vote[]
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * @param \Shopware\Struct\ListProduct[] $related
     */
    public function setRelated($related)
    {
        $this->related = $related;
    }

    /**
     * @return \Shopware\Struct\ListProduct[]
     */
    public function getRelated()
    {
        return $this->related;
    }

    /**
     * @param \Shopware\Struct\ListProduct[] $similar
     */
    public function setSimilar($similar)
    {
        $this->similar = $similar;
    }

    /**
     * @return \Shopware\Struct\ListProduct[]
     */
    public function getSimilar()
    {
        return $this->similar;
    }

    /**
     * @param \Shopware\Struct\Product\Download[] $downloads
     */
    public function setDownloads($downloads)
    {
        $this->downloads = $downloads;
    }

    /**
     * @return \Shopware\Struct\Product\Download[]
     */
    public function getDownloads()
    {
        return $this->downloads;
    }

    /**
     * @param \Shopware\Struct\Product\Link[] $links
     */
    public function setLinks($links)
    {
        $this->links = $links;
    }

    /**
     * @return \Shopware\Struct\Product\Link[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param \Shopware\Struct\Product\Configurator\Set $configurator
     */
    public function setConfigurator($configurator)
    {
        $this->configurator = $configurator;
    }

    /**
     * @return \Shopware\Struct\Product\Configurator\Set
     */
    public function getConfigurator()
    {
        return $this->configurator;
    }

    /**
     * @param \Shopware\Struct\Product\Configurator\Option[] $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return \Shopware\Struct\Product\Configurator\Option[]
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }


}