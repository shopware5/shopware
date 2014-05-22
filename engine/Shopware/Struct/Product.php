<?php

namespace Shopware\Struct;

use Shopware\Struct\Property\Set;

class Product extends ListProduct
{
    /**
     * @var ListProduct[]
     */
    private $relatedProducts;

    /**
     * @var ListProduct[]
     */
    private $similarProducts;

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
     * @param \Shopware\Struct\ListProduct[] $relatedProducts
     */
    public function setRelatedProducts($relatedProducts)
    {
        $this->relatedProducts = $relatedProducts;
    }

    /**
     * @return \Shopware\Struct\ListProduct[]
     */
    public function getRelatedProducts()
    {
        return $this->relatedProducts;
    }

    /**
     * @param \Shopware\Struct\ListProduct[] $similarProducts
     */
    public function setSimilarProducts($similarProducts)
    {
        $this->similarProducts = $similarProducts;
    }

    /**
     * @return \Shopware\Struct\ListProduct[]
     */
    public function getSimilarProducts()
    {
        return $this->similarProducts;
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
     * @param \Shopware\Struct\Configurator\Group[] $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return \Shopware\Struct\Configurator\Group[]
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }


}