<?php

declare(strict_types=1);
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

namespace Shopware\Components\Compatibility;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Enlight_Event_EventManager;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\CategoryServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Blog\Blog;
use Shopware\Bundle\StoreFrontBundle\Struct\Category;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Option;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Set as ConfiguratorSet;
use Shopware\Bundle\StoreFrontBundle\Struct\Country;
use Shopware\Bundle\StoreFrontBundle\Struct\Country\State;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Media;
use Shopware\Bundle\StoreFrontBundle\Struct\Payment;
use Shopware\Bundle\StoreFrontBundle\Struct\Product;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\MarketingAttribute;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Price;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Unit;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Vote;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\VoteAverage;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductStream;
use Shopware\Bundle\StoreFrontBundle\Struct\Property\Group as PropertyGroup;
use Shopware\Bundle\StoreFrontBundle\Struct\Property\Option as PropertyOption;
use Shopware\Bundle\StoreFrontBundle\Struct\Property\Set;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopPage;
use Shopware\Bundle\StoreFrontBundle\Struct\Thumbnail;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Emotion\Emotion;
use Shopware_Components_Config;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LegacyStructConverter
{
    private Shopware_Components_Config $config;

    private ContextServiceInterface $contextService;

    private Enlight_Event_EventManager $eventManager;

    private MediaServiceInterface $mediaService;

    private Connection $connection;

    private ModelManager $modelManager;

    private ContainerInterface $container;

    private CategoryServiceInterface $categoryService;

    public function __construct(
        Shopware_Components_Config $config,
        ContextServiceInterface $contextService,
        Enlight_Event_EventManager $eventManager,
        MediaServiceInterface $mediaService,
        Connection $connection,
        ModelManager $modelManager,
        CategoryServiceInterface $categoryService,
        ContainerInterface $container
    ) {
        $this->config = $config;
        $this->contextService = $contextService;
        $this->eventManager = $eventManager;
        $this->mediaService = $mediaService;
        $this->connection = $connection;
        $this->modelManager = $modelManager;
        $this->categoryService = $categoryService;
        $this->container = $container;
    }

    /**
     * @param Country[] $countries
     *
     * @return array
     */
    public function convertCountryStructList($countries)
    {
        return array_map([$this, 'convertCountryStruct'], $countries);
    }

    /**
     * @return array
     */
    public function convertCountryStruct(Country $country)
    {
        $data = json_decode(json_encode($country), true);
        $data = array_merge($data, [
            'countryname' => $country->getName(),
            'countryiso' => $country->getIso(),
            'countryen' => $country->getEn(),
            'position' => $country->getPosition(),
            'taxfree' => $country->isTaxFree(),
            'taxfree_ustid' => $country->isTaxFreeForVatId(),
            'taxfree_ustid_checked' => $country->checkVatId(),
            'active' => $country->isActive(),
            'iso3' => $country->getIso3(),
            'display_state_in_registration' => $country->displayStateSelection(),
            'force_state_in_registration' => $country->requiresStateSelection(),
            'areaID' => $country->getAreaId(),
            'allow_shipping' => $country->allowShipping(),
            'states' => [],
            'attributes' => $country->getAttributes(),
        ]);

        if ($country->displayStateSelection()) {
            $data['states'] = $this->convertStateStructList($country->getStates());
            $data['states'] = array_map(function ($state) use ($country) {
                $state['countryID'] = $country->getId();

                return $state;
            }, $data['states']);
        }

        if ($country->hasAttribute('core')) {
            $data['attribute'] = $country->getAttribute('core');
        }

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Country', $data, [
            'country' => $country,
        ]);
    }

    /**
     * @param State[]|null $states
     *
     * @return array
     */
    public function convertStateStructList($states)
    {
        if ($states === null) {
            return [];
        }

        return array_map([$this, 'convertStateStruct'], $states);
    }

    /**
     * @return array<string, mixed>
     */
    public function convertStateStruct(State $state)
    {
        /** @var array<string, mixed> $data */
        $data = json_decode(json_encode($state), true);
        $data += ['shortcode' => $state->getCode(), 'attributes' => $state->getAttributes()];

        if ($state->hasAttribute('core')) {
            $data['attribute'] = $state->getAttribute('core');
        }

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_State', $data, [
            'state' => $state,
        ]);
    }

    /**
     * Converts a configurator group struct which used for default or selection configurators.
     *
     * @return array
     */
    public function convertConfiguratorGroupStruct(Group $group)
    {
        $data = [
            'groupID' => $group->getId(),
            'groupname' => $group->getName(),
            'groupdescription' => $group->getDescription(),
            'selected_value' => null,
            'selected' => $group->isSelected(),
            'user_selected' => $group->isSelected(),
            'attributes' => $group->getAttributes(),
        ];

        if ($group->hasAttribute('core')) {
            $data['attribute'] = $group->getAttribute('core');
        }

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Configurator_Group', $data, [
            'configurator_group' => $group,
        ]);
    }

    /**
     * @return array
     */
    public function convertCategoryStruct(Category $category)
    {
        $media = null;
        if ($category->getMedia()) {
            $media = $this->convertMediaStruct($category->getMedia());
        }

        $attribute = [];
        if ($category->hasAttribute('core')) {
            $attribute = $category->getAttribute('core')->toArray();
        }

        $productStream = null;
        if ($category->getProductStream()) {
            $productStream = $this->convertRelatedProductStreamStruct($category->getProductStream());
        }

        $categoryPath = '|' . implode('|', $category->getPath()) . '|';

        $blogBaseUrl = $this->config->get('baseFile') . '?sViewport=blog&sCategory=';
        $baseUrl = $this->config->get('baseFile') . '?sViewport=cat&sCategory=';
        $detailUrl = ($category->isBlog() ? $blogBaseUrl : $baseUrl) . $category->getId();
        $canonicalParams = $this->getCategoryCanonicalParams($category);

        if ($media && !\array_key_exists('path', $media)) {
            $media['path'] = $media['source'];
        }

        $data = [
            'id' => $category->getId(),
            'parentId' => $category->getParentId(),
            'name' => $category->getName(),
            'position' => $category->getPosition(),
            'metaTitle' => $category->getMetaTitle(),
            'metaKeywords' => $category->getMetaKeywords(),
            'metaDescription' => $category->getMetaDescription(),
            'cmsHeadline' => $category->getCmsHeadline(),
            'cmsText' => $category->getCmsText(),
            'active' => true,
            'template' => $category->getTemplate(),
            'productBoxLayout' => $this->getProductBoxLayout($category),
            'blog' => $category->isBlog(),
            'path' => $categoryPath,
            'external' => $category->getExternalLink(),
            'externalTarget' => $category->getExternalTarget(),
            'hideFilter' => !$category->displayFacets(),
            'hideTop' => !$category->displayInNavigation(),
            'changed' => null,
            'added' => null,
            'attribute' => $attribute,
            'attributes' => $category->getAttributes(),
            'media' => $media,
            'mediaId' => $category->getMedia() ? $category->getMedia()->getId() : null,
            'link' => $this->getCategoryLink($category),
            'streamId' => $productStream ? $productStream['id'] : null,
            'productStream' => $productStream,
            'childrenCount' => $this->getCategoryChildrenCount($category->getId()),
            'description' => $category->getName(),
            'cmsheadline' => $category->getCmsHeadline(),
            'cmstext' => $category->getCmsText(),
            'sSelf' => $detailUrl,
            'canonicalParams' => $canonicalParams,
            'hide_sortings' => $category->hideSortings(),
            'rssFeed' => $detailUrl . '&sRss=1',
            'atomFeed' => $detailUrl . '&sAtom=1',
        ];

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Category', $data, [
            'category' => $category,
        ]);
    }

    /**
     * @param ListProduct[] $products
     *
     * @return array
     */
    public function convertListProductStructList(array $products)
    {
        return array_map([$this, 'convertListProductStruct'], $products);
    }

    /**
     * Converts the passed ListProduct struct to a shopware 3-4 array structure.
     *
     * @return array
     */
    public function convertListProductStruct(ListProduct $product)
    {
        $cheapestPrice = $product->getListingPrice();

        $promotion = $this->getListProductData($product);
        $promotion = array_merge($promotion, $this->convertProductPriceStruct($cheapestPrice));

        if ($product->getPriceGroup()) {
            $promotion['pricegroupActive'] = true;
            $promotion['pricegroupID'] = $product->getPriceGroup()->getId();
        }

        if ($product->displayFromPrice()) {
            $promotion['priceStartingFrom'] = $promotion['price'];
        }

        if ($product->getCover()) {
            $promotion['image'] = $this->convertMediaStruct($product->getCover());
        }

        if ($product->getVoteAverage()) {
            $promotion['sVoteAverage'] = $this->convertVoteAverageStruct($product->getVoteAverage());
        }

        $promotion['prices'] = [];
        foreach ($product->getPrices() as $price) {
            $promotion['prices'][] = $this->convertProductPriceStruct($price);
        }

        $promotion['linkBasket'] = $this->config->get('baseFile') .
            '?sViewport=basket&sAdd=' . $promotion['ordernumber'];

        $promotion['linkDetails'] = $this->config->get('baseFile') .
            '?sViewport=detail&sArticle=' . $promotion['articleID'];

        $promotion['linkVariant'] = $this->config->get('baseFile') .
            '?sViewport=detail&sArticle=' . $promotion['articleID'] . '&number=' . $promotion['ordernumber'];

        if ($this->config->get('useShortDescriptionInListing') && \strlen($promotion['description'] ?? '') > 5) {
            $promotion['description_long'] = $promotion['description'];
        }

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_List_Product', $promotion, [
            'product' => $product,
        ]);
    }

    /**
     * @return array
     */
    public function convertProductPriceStruct(Price $price)
    {
        $data = $this->convertPriceStruct($price);
        $data['pseudopricePercent'] = null;
        $data['price'] = $this->formatPrice($price->getCalculatedPrice());
        $data['pseudoprice'] = $this->formatPrice($price->getCalculatedPseudoPrice());
        $data['referenceprice'] = $this->formatPrice($price->getCalculatedReferencePrice());
        $data['has_pseudoprice'] = $price->getCalculatedPseudoPrice() > $price->getCalculatedPrice();
        $data['price_numeric'] = $price->getCalculatedPrice();
        $data['pseudoprice_numeric'] = $price->getCalculatedPseudoPrice();
        $data['price_attributes'] = $price->getAttributes();
        $data['pricegroup'] = $price->getCustomerGroup()->getKey();
        $data['regulationPrice'] = $price->getCalculatedRegulationPrice();

        if ($price->getCalculatedPseudoPrice()) {
            $discount = 0;
            if ($price->getCalculatedPseudoPrice() != 0) {
                $discount = round(($price->getCalculatedPrice() / $price->getCalculatedPseudoPrice() * 100) - 100, 2) * -1;
            }

            $data['pseudopricePercent'] = [
                'int' => round($discount),
                'float' => $discount,
            ];
        }

        // Reset unit data
        $data['minpurchase'] = null;
        $data['maxpurchase'] = $this->config->get('maxPurchase');
        $data['purchasesteps'] = 1;
        $data['purchaseunit'] = null;
        $data['referenceunit'] = null;
        $data['packunit'] = null;
        $data['unitID'] = null;
        $data['sUnit'] = ['unit' => '', 'description' => ''];
        $data['unit_attributes'] = [];

        if ($price->getUnit() instanceof Unit) {
            $data = array_merge($data, $this->convertUnitStruct($price->getUnit()));
        }

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Product_Price', $data, [
            'price' => $price,
        ]);
    }

    /**
     * Converts the passed ProductStream struct to an array structure.
     *
     * @return array
     */
    public function convertRelatedProductStreamStruct(ProductStream $productStream)
    {
        $data = [
            'id' => $productStream->getId(),
            'name' => $productStream->getName(),
            'description' => $productStream->getDescription(),
            'type' => $productStream->getType(),
            'attributes' => $productStream->getAttributes(),
        ];

        if ($productStream->hasAttribute('core')) {
            $data['attribute'] = $productStream->getAttribute('core');
        }

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Related_Product_Stream', $data, [
            'product_stream' => $productStream,
        ]);
    }

    /**
     * @return array
     */
    public function convertProductStruct(Product $product)
    {
        $data = $this->getListProductData($product);

        if ($product->getUnit()) {
            $data = array_merge($data, $this->convertUnitStruct($product->getUnit()));
        }

        if ($product->getPriceGroup()) {
            $data = array_merge(
                $data,
                [
                    'pricegroupActive' => $product->isPriceGroupActive(),
                    'pricegroupID' => $product->getPriceGroup()->getId(),
                    'pricegroup_attributes' => $product->getPriceGroup()->getAttributes(),
                ]
            );
        }

        $variantPrice = $product->getVariantPrice();
        $data = array_merge($data, $this->convertProductPriceStruct($variantPrice));
        $data['referenceprice'] = $variantPrice->getCalculatedReferencePrice();
        $data['pricegroup'] = $variantPrice->getCustomerGroup()->getKey();

        if (\count($product->getPrices()) > 1) {
            foreach ($product->getPrices() as $price) {
                $data['sBlockPrices'][] = $this->convertPriceStruct(
                    $price
                );
            }
        }

        // Convert all product images and set cover image
        foreach ($product->getMedia() as $media) {
            $data['images'][] = $this->convertMediaStruct($media);
        }

        if (empty($data['images'])) {
            if ($product->getCover()) {
                $data['image'] = $this->convertMediaStruct($product->getCover());
            }
        } else {
            $data['image'] = array_shift($data['images']);
        }

        // Convert product voting
        foreach ($product->getVotes() as $vote) {
            $data['sVoteComments'][] = $this->convertVoteStruct($vote);
        }

        $data['sVoteAverage'] = ['average' => 0, 'count' => 0];

        if ($product->getVoteAverage()) {
            $data['sVoteAverage'] = $this->convertVoteAverageStruct($product->getVoteAverage());
        }

        if ($product->getPropertySet()) {
            $data['filtergroupID'] = $product->getPropertySet()->getId();
            $data['sProperties'] = $this->convertPropertySetStruct($product->getPropertySet());
        }

        foreach ($product->getDownloads() as $download) {
            $data['sDownloads'][] = [
                'id' => $download->getId(),
                'description' => $download->getDescription(),
                'filename' => $this->mediaService->getUrl($download->getFile()),
                'size' => $download->getSize(),
                'attributes' => $download->getAttributes(),
            ];
        }

        foreach ($product->getLinks() as $link) {
            $temp = [
                'id' => $link->getId(),
                'description' => $link->getDescription(),
                'link' => $link->getLink(),
                'target' => $link->getTarget(),
                'supplierSearch' => false,
                'attributes' => $link->getAttributes(),
            ];

            if (strpos($temp['link'], 'http') === false) {
                $temp['link'] = 'http://' . $link->getLink();
            }

            $data['sLinks'][] = $temp;
        }

        $data['sLinks'][] = [
            'supplierSearch' => true,
            'description' => $product->getManufacturer()->getName(),
            'target' => '_parent',
            'link' => $this->getSupplierListingLink($product->getManufacturer()),
        ];

        $data['sRelatedArticles'] = [];
        foreach ($product->getRelatedProducts() as $relatedProduct) {
            $data['sRelatedArticles'][] = $this->convertListProductStruct($relatedProduct);
        }

        $data['sSimilarArticles'] = [];
        foreach ($product->getSimilarProducts() as $similarProduct) {
            $data['sSimilarArticles'][] = $this->convertListProductStruct($similarProduct);
        }

        $data['relatedProductStreams'] = [];
        foreach ($product->getRelatedProductStreams() as $relatedProductStream) {
            $data['relatedProductStreams'][] = $this->convertRelatedProductStreamStruct($relatedProductStream);
        }

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Product', $data, [
            'product' => $product,
        ]);
    }

    /**
     * @return array
     */
    public function convertVoteAverageStruct(VoteAverage $average)
    {
        $data = [
            'average' => round($average->getAverage(), 1),
            'count' => $average->getCount(),
            'pointCount' => $average->getPointCount(),
            'attributes' => $average->getAttributes(),
        ];

        if ($average->hasAttribute('core')) {
            $data['attribute'] = $average->getAttribute('core');
        }

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Vote_Average', $data, [
            'average' => $average,
        ]);
    }

    /**
     * @return array
     */
    public function convertVoteStruct(Vote $vote)
    {
        $data = [
            'id' => $vote->getId(),
            'name' => $vote->getName(),
            'headline' => $vote->getHeadline(),
            'comment' => $vote->getComment(),
            'points' => $vote->getPoints(),
            'active' => true,
            'email' => $vote->getEmail(),
            'answer' => $vote->getAnswer(),
            'datum' => '0000-00-00 00:00:00',
            'answer_date' => '0000-00-00 00:00:00',
            'attributes' => $vote->getAttributes(),
        ];

        if ($vote->hasAttribute('core')) {
            $data['attribute'] = $vote->getAttribute('core');
        }

        if ($vote->getCreatedAt() instanceof DateTime) {
            $data['datum'] = $vote->getCreatedAt()->format('Y-m-d H:i:s');
        }

        if ($vote->getAnsweredAt() instanceof DateTime) {
            $data['answer_date'] = $vote->getAnsweredAt()->format('Y-m-d H:i:s');
        }

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Vote', $data, [
            'vote' => $vote,
        ]);
    }

    /**
     * @return array
     */
    public function convertPriceStruct(Price $price)
    {
        $data = [
            'valFrom' => $price->getFrom(),
            'valTo' => $price->getTo(),
            'from' => $price->getFrom(),
            'to' => $price->getTo(),
            'price' => $price->getCalculatedPrice(),
            'pseudoprice' => $price->getCalculatedPseudoPrice(),
            'referenceprice' => $price->getCalculatedReferencePrice(),
        ];

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Price', $data, [
            'price' => $price,
        ]);
    }

    /**
     * @return array
     */
    public function convertMediaStruct(?Media $media = null)
    {
        if (!$media instanceof Media) {
            return [];
        }

        $thumbnails = [];

        foreach ($media->getThumbnails() as $thumbnail) {
            $thumbnails[] = [
                'source' => $thumbnail->getSource(),
                'retinaSource' => $thumbnail->getRetinaSource(),
                'sourceSet' => $this->getSourceSet($thumbnail),
                'maxWidth' => $thumbnail->getMaxWidth(),
                'maxHeight' => $thumbnail->getMaxHeight(),
                'attributes' => $thumbnail->getAttributes(),
            ];
        }

        $data = [
            'id' => $media->getId(),
            'position' => null,
            'source' => $media->getFile(),
            'description' => $media->getName(),
            'extension' => $media->getExtension(),
            'main' => $media->isPreview(),
            'parentId' => null,
            'width' => $media->getWidth(),
            'height' => $media->getHeight(),
            'thumbnails' => $thumbnails,
            'attributes' => $media->getAttributes(),
        ];

        if ($media->hasAttribute('core')) {
            $data['attribute'] = $media->getAttribute('core');
        }

        $attributes = $media->getAttributes();
        if ($attributes && isset($attributes['image'])) {
            $data['attribute'] = $attributes['image']->toArray();
            unset($data['attribute']['id'], $data['attribute']['imageID']);
        } else {
            $data['attribute'] = [];
        }

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Media', $data, [
            'media' => $media,
        ]);
    }

    /**
     * @return array
     */
    public function convertUnitStruct(Unit $unit)
    {
        $data = [
            'minpurchase' => $unit->getMinPurchase(),
            'maxpurchase' => $unit->getMaxPurchase() ?: $this->config->get('maxPurchase'),
            'purchasesteps' => $unit->getPurchaseStep() ?: 1,
            'purchaseunit' => $unit->getPurchaseUnit(),
            'referenceunit' => $unit->getReferenceUnit(),
            'packunit' => $unit->getPackUnit(),
            'unitID' => $unit->getId(),
            'sUnit' => [
                'unit' => $unit->getUnit(),
                'description' => $unit->getName(),
            ],
            'unit_attributes' => $unit->getAttributes(),
        ];

        if ($unit->hasAttribute('core')) {
            $data['unit_attribute'] = $unit->getAttribute('core');
        }

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Unit', $data, [
            'unit' => $unit,
        ]);
    }

    /**
     * @return string
     */
    public function getSupplierListingLink(Manufacturer $manufacturer)
    {
        return 'controller=listing&action=manufacturer&sSupplier=' . (int) $manufacturer->getId();
    }

    /**
     * Example:
     *
     * return [
     *     9 => [
     *         'id' => 9,
     *         'optionID' => 9,
     *         'name' => 'Farbe',
     *         'groupID' => 1,
     *         'groupName' => 'Edelbrände',
     *         'value' => 'goldig',
     *         'values' => [
     *             53 => 'goldig',
     *         ],
     *     ],
     *     2 => [
     *         'id' => 2,
     *         'optionID' => 2,
     *         'name' => 'Flaschengröße',
     *         'groupID' => 1,
     *         'groupName' => 'Edelbrände',
     *         'value' => '0,5 Liter, 0,7 Liter, 1,0 Liter',
     *         'values' => [
     *             23 => '0,5 Liter',
     *             24 => '0,7 Liter',
     *             25 => '1,0 Liter',
     *         ],
     *     ],
     * ];
     *
     * @return array
     */
    public function convertPropertySetStruct(Set $set)
    {
        $result = [];
        foreach ($set->getGroups() as $group) {
            $values = [];
            foreach ($group->getOptions() as $option) {
                $values[$option->getId()] = $option->getName();
            }

            $propertyOptions = array_map([$this, 'convertPropertyOptionStruct'], $group->getOptions());

            $mediaValues = [];
            foreach ($group->getOptions() as $option) {
                if ($option->getMedia()) {
                    $mediaValues[$option->getId()] = array_merge(['valueId' => $option->getId()], $this->convertMediaStruct($option->getMedia()));
                }
            }

            $groupId = $group->getId();
            $result[$groupId] = [
                'id' => $groupId,
                'optionID' => $groupId,
                'name' => $group->getName(),
                'groupID' => $set->getId(),
                'groupName' => $set->getName(),
                'value' => implode(', ', $values),
                'values' => $values,
                'isFilterable' => $group->isFilterable(),
                'options' => $propertyOptions,
                'media' => $mediaValues,
                'attributes' => $group->getAttributes(),
            ];

            if ($group->hasAttribute('core')) {
                $result[$groupId]['attribute'] = $group->getAttribute('core');
            }
        }

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Property_Set', $result, [
            'property_set' => $set,
        ]);
    }

    /**
     * @return array
     */
    public function convertPropertyGroupStruct(PropertyGroup $group)
    {
        $data = [
            'id' => $group->getId(),
            'name' => $group->getName(),
            'isFilterable' => $group->isFilterable(),
            'options' => [],
            'attributes' => $group->getAttributes(),
        ];

        if ($group->hasAttribute('core')) {
            $data['attribute'] = $group->getAttribute('core');
        }

        foreach ($group->getOptions() as $option) {
            $data['options'][] = $this->convertPropertyOptionStruct($option);
        }

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Property_Group', $data, [
            'property_group' => $group,
        ]);
    }

    /**
     * @return array
     */
    public function convertPropertyOptionStruct(PropertyOption $option)
    {
        $data = [
            'id' => $option->getId(),
            'name' => $option->getName(),
            'attributes' => $option->getAttributes(),
        ];

        if ($option->hasAttribute('core')) {
            $data['attribute'] = $option->getAttribute('core');
        }

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Property_Option', $data, [
            'property_option' => $option,
        ]);
    }

    /**
     * @return array
     */
    public function convertManufacturerStruct(Manufacturer $manufacturer)
    {
        $data = [
            'id' => $manufacturer->getId(),
            'name' => $manufacturer->getName(),
            'description' => $manufacturer->getDescription(),
            'metaTitle' => $manufacturer->getMetaTitle(),
            'metaDescription' => $manufacturer->getMetaDescription(),
            'metaKeywords' => $manufacturer->getMetaKeywords(),
            'link' => $manufacturer->getLink(),
            'image' => $manufacturer->getCoverFile(),
            'media' => $manufacturer->getCoverMedia() ? $this->convertMediaStruct($manufacturer->getCoverMedia()) : null,
            'attributes' => $manufacturer->getAttributes(),
        ];

        if ($manufacturer->hasAttribute('core')) {
            $data['attribute'] = $manufacturer->getAttribute('core');
        }

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Manufacturer', $data, [
            'manufacturer' => $manufacturer,
        ]);
    }

    /**
     * @return array
     */
    public function convertConfiguratorStruct(ListProduct $product, ConfiguratorSet $set)
    {
        $groups = [];
        foreach ($set->getGroups() as $group) {
            $groupData = $this->convertConfiguratorGroupStruct($group);

            $options = [];
            foreach ($group->getOptions() as $option) {
                $optionData = $this->convertConfiguratorOptionStruct(
                    $group,
                    $option
                );

                if ($option->isSelected()) {
                    $groupData['selected_value'] = $option->getId();
                }

                $options[$option->getId()] = $optionData;
            }

            $groupData['values'] = $options;
            $groups[] = $groupData;
        }

        $settings = $this->getConfiguratorSettings($set, $product);

        $data = [
            'sConfigurator' => $groups,
            'sConfiguratorSettings' => $settings,
            'isSelectionSpecified' => $set->isSelectionSpecified(),
        ];

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Configurator_Set', $data, [
            'configurator_set' => $set,
            'product' => $product,
        ]);
    }

    /**
     * @return array
     */
    public function convertConfiguratorPrice(ListProduct $product, ConfiguratorSet $set)
    {
        if ($set->isSelectionSpecified()) {
            return [];
        }

        $data = [];

        $variantPrice = $product->getVariantPrice();
        $cheapestPrice = $product->getListingPrice();

        if (\count($product->getPrices()) > 1 || $product->hasDifferentPrices()) {
            $data['priceStartingFrom'] = $this->formatPrice($cheapestPrice->getCalculatedPrice());
        }

        $data = array_merge($data, $this->convertProductPriceStruct($cheapestPrice));
        $data['price'] = $data['priceStartingFrom'] ?: $this->formatPrice($variantPrice->getCalculatedPrice());
        $data['sBlockPrices'] = [];

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Configurator_Price', $data, [
            'configurator_set' => $set,
            'product' => $product,
        ]);
    }

    /**
     * Creates the settings array for the passed configurator set
     *
     * @return array
     */
    public function getConfiguratorSettings(ConfiguratorSet $set, ListProduct $product)
    {
        $settings = [
            'instock' => $product->isCloseouts(),
            'articleID' => $product->getId(),
            'type' => $set->getType(),
            'attributes' => $set->getAttributes(),
        ];

        if ($set->hasAttribute('core')) {
            $data['attribute'] = $set->getAttribute('core');
        }

        // Switch the template for the different configurator types.
        if ($set->getType() == 1) {
            // Selection configurator
            $settings['template'] = 'article_config_step.tpl';
        } elseif ($set->getType() == 2) {
            // Table configurator
            $settings['template'] = 'article_config_picture.tpl';
        } else {
            // Other configurator types
            $settings['template'] = 'article_config_upprice.tpl';
        }

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Configurator_Settings', $settings, [
            'configurator_set' => $set,
            'product' => $product,
        ]);
    }

    /**
     * Converts a configurator option struct which used for default or selection configurators.
     *
     * @return array
     */
    public function convertConfiguratorOptionStruct(Group $group, Option $option)
    {
        $data = [
            'optionID' => $option->getId(),
            'groupID' => $group->getId(),
            'optionname' => $option->getName(),
            'user_selected' => $option->isSelected(),
            'selected' => $option->isSelected(),
            'selectable' => $option->getActive(),
            'attributes' => $option->getAttributes(),
        ];

        if ($option->getMedia()) {
            $data['media'] = $this->convertMediaStruct($option->getMedia());
        }

        if ($option->hasAttribute('core')) {
            $data['attribute'] = $option->getAttribute('core');
        }

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Configurator_Option', $data, [
            'configurator_group' => $group,
            'configurator_options' => $option,
        ]);
    }

    /**
     * @return array
     */
    public function convertBlogStruct(Blog $blog)
    {
        $data = [
            'id' => $blog->getId(),
            'title' => $blog->getTitle(),
            'authorId' => $blog->getAuthorId(),
            'active' => $blog->isActive(),
            'shortDescription' => $blog->getShortDescription(),
            'description' => $blog->getDescription(),
            'displayDate' => $blog->getDisplayDate(),
            'categoryId' => $blog->getCategoryId(),
            'template' => $blog->getTemplate(),
            'metaKeyWords' => $blog->getMetaKeywords(),
            'metaKeywords' => $blog->getMetaKeywords(),
            'metaDescription' => $blog->getMetaDescription(),
            'metaTitle' => $blog->getMetaTitle(),
            'views' => $blog->getViews(),
            'mediaList' => array_map([$this, 'convertMediaStruct'], $blog->getMedias()),
            'attributes' => $blog->getAttributes(),
        ];

        if ($blog->hasAttribute('core')) {
            $data['attribute'] = $blog->getAttribute('core');
        }

        $data['media'] = reset($data['mediaList']);

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Blog', $data, [
            'blog' => $blog,
        ]);
    }

    /**
     * Converts a payment struct
     *
     * @return array
     */
    public function convertPaymentStruct(Payment $payment)
    {
        $data = [
            'id' => $payment->getId(),
            'name' => $payment->getName(),
            'description' => $payment->getDescription(),
            'template' => $payment->getTemplate(),
            'class' => $payment->getClass(),
            'table' => $payment->getTable(),
            'hide' => $payment->getHide(),
            'additionaldescription' => $payment->getAdditionalDescription(),
            'debit_percent' => $payment->getDebitPercent(),
            'surcharge' => $payment->getSurcharge(),
            'surchargestring' => $payment->getSurchargeString(),
            'position' => $payment->getPosition(),
            'active' => $payment->getActive(),
            'esdactive' => $payment->getEsdActive(),
            'embediframe' => $payment->getEmbediframe(),
            'hideprospect' => $payment->getHideProspect(),
            'action' => $payment->getAction(),
            'pluginID' => $payment->getPluginID(),
            'source' => $payment->getSource(),
            'mobile_inactive' => $payment->getMobileInactive(),
            'attributes' => $payment->getAttributes(),
        ];

        if ($payment->hasAttribute('core')) {
            $data['attribute'] = $payment->getAttribute('core');
        }

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Payment', $data, [
            'payment' => $payment,
        ]);
    }

    /**
     * @return array
     */
    public function convertShopPageStruct(ShopPage $shopPage)
    {
        $data = $shopPage->jsonSerialize();

        $data += [
            'attributes' => $shopPage->getAttributes(),
        ];

        if ($shopPage->hasAttribute('core')) {
            $data['attribute'] = $shopPage->getAttribute('core')->jsonSerialize();
        }

        $data['children'] = $this->convertShopPageStructList($shopPage->getChildren());
        $data['parentID'] = $shopPage->getParentId();

        return $data;
    }

    /**
     * @param ShopPage[] $shopPages
     *
     * @return array
     */
    public function convertShopPageStructList(array $shopPages)
    {
        return array_map([$this, 'convertShopPageStruct'], $shopPages);
    }

    /**
     * Returns the count of children categories of the provided category
     */
    private function getCategoryChildrenCount(int $id): int
    {
        return (int) $this->connection->fetchColumn(
            'SELECT count(category.id) FROM s_categories category WHERE parent = :id',
            ['id' => $id]
        );
    }

    private function getCategoryLink(Category $category): string
    {
        $viewport = $category->isBlog() ? 'blog' : 'cat';
        $params = http_build_query(
            ['sViewport' => $viewport, 'sCategory' => $category->getId()],
            '',
            '&'
        );

        return $this->config->get('baseFile') . '?' . $params;
    }

    private function getSourceSet(Thumbnail $thumbnail): string
    {
        if ($thumbnail->getRetinaSource() !== null) {
            return sprintf('%s, %s 2x', $thumbnail->getSource(), $thumbnail->getRetinaSource());
        }

        return $thumbnail->getSource();
    }

    /**
     * Formats article prices
     */
    private function formatPrice(?float $price): string
    {
        $price = str_replace(',', '.', (string) $price);
        $price = $this->round($price);
        $price = str_replace('.', ',', (string) $price); // Replace points with commas
        $commaPos = strpos($price, ',');
        if ($commaPos) {
            $part = substr($price, $commaPos + 1, \strlen($price) - $commaPos);
            switch (\strlen($part)) {
                case 1:
                    $price .= '0';
                    break;
                case 2:
                    break;
            }
        } else {
            if (!$price) {
                $price = '0';
            } else {
                $price .= ',00';
            }
        }

        return $price;
    }

    private function round(string $amount): float
    {
        $amountStr = explode('.', $amount);
        if (empty($amountStr[1])) {
            $amountStr[1] = 0;
        }
        $amountStr[1] = substr((string) $amountStr[1], 0, 3); // Rounded to the nearest thousandth as a string

        $amountStr = $amountStr[0] . '.' . $amountStr[1];

        return round((float) $amountStr, 2);
    }

    /**
     * Internal function which converts only the data of a list product.
     * Associated data won't be converted.
     */
    private function getListProductData(ListProduct $product): array
    {
        $createDate = null;
        if ($product->getCreatedAt()) {
            $createDate = $product->getCreatedAt()->format('Y-m-d');
        }
        $updateDate = null;
        if ($product->getUpdatedAt()) {
            $updateDate = $product->getUpdatedAt()->format('Y-m-d');
        }

        $data = [
            'articleID' => $product->getId(),
            'articleDetailsID' => $product->getVariantId(),
            'ordernumber' => $product->getNumber(),
            'highlight' => $product->highlight(),
            'description' => $product->getShortDescription(),
            'description_long' => $product->getLongDescription(),
            'esd' => $product->hasEsd(),
            'articleName' => $product->getName(),
            'taxID' => $product->getTax()->getId(),
            'tax' => $product->getTax()->getTax(),
            'instock' => $product->getStock(),
            'isAvailable' => $product->isAvailable(),
            'hasAvailableVariant' => $product->hasAvailableVariant(),
            'weight' => $product->getWeight(),
            'shippingtime' => $product->getShippingTime(),
            'pricegroupActive' => false,
            'pricegroupID' => null,
            'length' => $product->getLength(),
            'height' => $product->getHeight(),
            'width' => $product->getWidth(),
            'laststock' => $product->isCloseouts(),
            'additionaltext' => $product->getAdditional(),
            'datum' => $createDate,
            'update' => $updateDate,
            'sales' => $product->getSales(),
            'filtergroupID' => null,
            'priceStartingFrom' => null,
            'pseudopricePercent' => null,
            // Flag inside mini product
            'sVariantArticle' => null,
            'sConfigurator' => $product->hasConfigurator(),
            // Only used for full products
            'metaTitle' => $product->getMetaTitle(),
            'shippingfree' => $product->isShippingFree(),
            'suppliernumber' => $product->getManufacturerNumber(),
            'notification' => $product->allowsNotification(),
            'ean' => trim((string) $product->getEan()),
            'keywords' => $product->getKeywords(),
            'sReleasedate' => $this->dateToString($product->getReleaseDate()),
            'template' => $product->getTemplate(),
            'attributes' => $product->getAttributes(),
            'allowBuyInListing' => $product->allowBuyInListing(),
        ];

        if ($product->hasAttribute('core')) {
            $attributes = $product->getAttribute('core')->toArray();
            unset($attributes['id'], $attributes['articleID'], $attributes['articledetailsID']);

            $data = array_merge($data, $attributes);
        }

        if ($product->getManufacturer()) {
            $manufacturer = [
                'supplierName' => $product->getManufacturer()->getName(),
                'supplierImg' => $product->getManufacturer()->getCoverFile(),
                'supplierID' => $product->getManufacturer()->getId(),
                'supplierDescription' => $product->getManufacturer()->getDescription(),
                'supplierMedia' => $product->getManufacturer()->getCoverMedia() ? $this->convertMediaStruct($product->getManufacturer()->getCoverMedia()) : null,
            ];

            $data = array_merge($data, $manufacturer);
            $data['supplier_attributes'] = $product->getManufacturer()->getAttributes();
        }

        $marketing = $product->getAttribute('marketing');
        if ($marketing instanceof MarketingAttribute) {
            $data['newArticle'] = $marketing->isNew();
            $data['sUpcoming'] = $marketing->comingSoon();
            $data['topseller'] = $marketing->isTopSeller();
        }

        $today = new DateTime();
        if ($product->getReleaseDate() && $product->getReleaseDate() > $today) {
            $data['sReleasedate'] = $product->getReleaseDate()->format('Y-m-d');
        }

        return $this->eventManager->filter('Legacy_Struct_Converter_List_Product_Data', $data, [
            'product' => $product,
        ]);
    }

    /**
     * @param DateTimeInterface|string $date
     */
    private function dateToString($date): string
    {
        if ($date instanceof DateTime) {
            return $date->format('Y-m-d');
        }

        return '';
    }

    private function getProductBoxLayout(?Category $category = null): string
    {
        if (!$category instanceof Category) {
            return 'basic';
        }

        if ($category->getProductBoxLayout() !== 'extend' && $category->getProductBoxLayout() !== null) {
            return $category->getProductBoxLayout();
        }

        $category = $this->categoryService->get($category->getParentId(), $this->contextService->getShopContext());

        return $this->getProductBoxLayout($category);
    }

    /**
     * @return array{sViewport: 'blog'|'cat', sCategory: int, sPage?: int}
     */
    private function getCategoryCanonicalParams(Category $category): array
    {
        $page = (int) $this->container->get('front')->Request()->getQuery('sPage');

        $emotion = $this->modelManager->getRepository(Emotion::class)
            ->getCategoryBaseEmotionsQuery($category->getId())
            ->getArrayResult();

        $canonicalParams = [
            'sViewport' => $category->isBlog() ? 'blog' : 'cat',
            'sCategory' => $category->getId(),
        ];

        /*
         * Only include page parameter in canonical if...
         * a) we are on a page > 1
         * b) we are on the page 1 and the category has a ShoppingWorld (so /category and /category?p=1 show different content
         */
        if ($page > 1 || ($emotion && $page === 1)) {
            $canonicalParams['sPage'] = $page;
        }

        return $canonicalParams;
    }
}
