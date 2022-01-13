<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\StoreFrontBundle\Struct;

use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Download;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Link;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Vote;
use Shopware\Bundle\StoreFrontBundle\Struct\Property\Set;

class Product extends ListProduct
{
    /**
     * @var ListProduct[]
     */
    protected $relatedProducts = [];

    /**
     * @var ProductStream[]
     */
    protected $relatedProductStreams = [];

    /**
     * @var ListProduct[]
     */
    protected $similarProducts = [];

    /**
     * @var Product\Download[]
     */
    protected $downloads = [];

    /**
     * @var Product\Link[]
     */
    protected $links = [];

    /**
     * @var Media[]
     */
    protected $media = [];

    /**
     * @var Product\Vote[]
     */
    protected $votes = [];

    /**
     * @var Set
     */
    protected $propertySet;

    /**
     * @var Group[]
     */
    protected $configuration = [];

    /**
     * @return Product
     */
    public static function createFromListProduct(ListProduct $listProduct)
    {
        $product = new self(
            $listProduct->getId(),
            $listProduct->getVariantId(),
            $listProduct->getNumber()
        );
        foreach ($listProduct as $key => $value) {
            $product->$key = $value;
        }

        return $product;
    }

    /**
     * @param Media[] $media
     */
    public function setMedia($media)
    {
        $this->media = $media;
    }

    /**
     * @return Media[]
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param int $index
     *
     * @return Thumbnail[]
     */
    public function getThumbnailsBySize($index)
    {
        $result = array_filter($this->media, function (Media $media) {
            return $media->getType() === Media::TYPE_IMAGE;
        });

        return array_map(function (Media $media) use ($index) {
            return $media->getThumbnail($index);
        }, $result);
    }

    /**
     * @param Set $propertySet
     */
    public function setPropertySet($propertySet)
    {
        $this->propertySet = $propertySet;
    }

    /**
     * @return Set|null
     */
    public function getPropertySet()
    {
        return $this->propertySet;
    }

    /**
     * @param Vote[] $votes
     */
    public function setVotes($votes)
    {
        $this->votes = $votes;
    }

    /**
     * @return Vote[]
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * @param ListProduct[] $relatedProducts
     */
    public function setRelatedProducts($relatedProducts)
    {
        $this->relatedProducts = $relatedProducts;
    }

    /**
     * @return ListProduct[]
     */
    public function getRelatedProducts()
    {
        return $this->relatedProducts;
    }

    /**
     * @return ProductStream[]
     */
    public function getRelatedProductStreams()
    {
        return $this->relatedProductStreams;
    }

    /**
     * @param ProductStream[] $relatedProductStreams
     */
    public function setRelatedProductStreams($relatedProductStreams)
    {
        $this->relatedProductStreams = $relatedProductStreams;
    }

    /**
     * @param ListProduct[] $similarProducts
     */
    public function setSimilarProducts($similarProducts)
    {
        $this->similarProducts = $similarProducts;
    }

    /**
     * @return ListProduct[]
     */
    public function getSimilarProducts()
    {
        return $this->similarProducts;
    }

    /**
     * @param Download[] $downloads
     */
    public function setDownloads($downloads)
    {
        $this->downloads = $downloads;
    }

    /**
     * @return Download[]
     */
    public function getDownloads()
    {
        return $this->downloads;
    }

    /**
     * @param Link[] $links
     */
    public function setLinks($links)
    {
        $this->links = $links;
    }

    /**
     * @return Link[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param Group[] $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return Group[]
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Helper function which used to get the configuration selection of
     * the passed product number.
     * The result array contains a simple array which elements are indexed by
     * the configurator group id and the value contains the configurator option id.
     *
     * This function is required to load different product variations on the product
     * detail page via order number.
     *
     * @return array<int, int>
     */
    public function getSelectedOptions()
    {
        $selection = [];

        foreach ($this->configuration as $group) {
            $selection[$group->getId()] = $group->getOptions()[0]->getId();
        }

        return $selection;
    }
}
