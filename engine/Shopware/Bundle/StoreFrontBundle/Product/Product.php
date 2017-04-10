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

namespace Shopware\Bundle\StoreFrontBundle\Product;

use Shopware\Bundle\StoreFrontBundle\Media\Media;
use Shopware\Bundle\StoreFrontBundle\ProductStream\ProductStream;
use Shopware\Bundle\StoreFrontBundle\Property\PropertySet;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
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
     * @var \Shopware\Bundle\StoreFrontBundle\ProductDownload\Download[]
     */
    protected $downloads = [];

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\ProductLink\Link[]
     */
    protected $links = [];

    /**
     * @var Media[]
     */
    protected $media = [];

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Vote\Vote[]
     */
    protected $votes = [];

    /**
     * @var PropertySet
     */
    protected $propertySet;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Configurator\ConfiguratorGroup[]
     */
    protected $configuration = [];

    /**
     * @param ListProduct $listProduct
     *
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
     * @param \Shopware\Bundle\StoreFrontBundle\Media\Media[] $media
     */
    public function setMedia($media)
    {
        $this->media = $media;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Media\Media[]
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param int $index
     *
     * @return \Shopware\Bundle\StoreFrontBundle\Media\Thumbnail[]
     */
    public function getThumbnailsBySize($index)
    {
        /** @var $media Media */
        $result = array_filter($this->media, function (Media $media) {
            return $media->getType() === Media::TYPE_IMAGE;
        });

        return array_map(function (Media $media) use ($index) {
            return $media->getThumbnail($index);
        }, $result);
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Property\PropertySet $propertySet
     */
    public function setPropertySet($propertySet)
    {
        $this->propertySet = $propertySet;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Property\PropertySet
     */
    public function getPropertySet()
    {
        return $this->propertySet;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Vote\Vote[] $votes
     */
    public function setVotes($votes)
    {
        $this->votes = $votes;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Vote\Vote[]
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Product\ListProduct[] $relatedProducts
     */
    public function setRelatedProducts($relatedProducts)
    {
        $this->relatedProducts = $relatedProducts;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Product\ListProduct[]
     */
    public function getRelatedProducts()
    {
        return $this->relatedProducts;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\ProductStream\ProductStream[]
     */
    public function getRelatedProductStreams()
    {
        return $this->relatedProductStreams;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\ProductStream\ProductStream[] $relatedProductStreams
     */
    public function setRelatedProductStreams($relatedProductStreams)
    {
        $this->relatedProductStreams = $relatedProductStreams;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Product\ListProduct[] $similarProducts
     */
    public function setSimilarProducts($similarProducts)
    {
        $this->similarProducts = $similarProducts;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Product\ListProduct[]
     */
    public function getSimilarProducts()
    {
        return $this->similarProducts;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\ProductDownload\Download[] $downloads
     */
    public function setDownloads($downloads)
    {
        $this->downloads = $downloads;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\ProductDownload\Download[]
     */
    public function getDownloads()
    {
        return $this->downloads;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\ProductLink\Link[] $links
     */
    public function setLinks($links)
    {
        $this->links = $links;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\ProductLink\Link[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Configurator\ConfiguratorGroup[] $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Configurator\ConfiguratorGroup[]
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
     * @return array
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
