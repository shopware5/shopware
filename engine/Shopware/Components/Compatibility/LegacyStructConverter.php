<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

use Shopware\Bundle\SearchBundle;
use Shopware\Bundle\StoreFrontBundle;

class LegacyStructConverter
{
    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var LegacyEventManager
     */
    private $legacyEventManager;

    function __construct(
        \Shopware_Components_Config $config,
        LegacyEventManager $legacyEventManager
    ) {
        $this->config = $config;
        $this->legacyEventManager = $legacyEventManager;
    }

    /**
     * Converts a configurator group struct which used for default or selection configurators.
     *
     * @param StoreFrontBundle\Struct\Configurator\Group $group
     * @return array
     */
    public function convertConfiguratorGroupStruct(StoreFrontBundle\Struct\Configurator\Group $group)
    {
        return array(
            'groupID' => $group->getId(),
            'groupname' => $group->getName(),
            'groupdescription' => $group->getDescription(),
            'selected_value' => null,
            'selected' => $group->isSelected(),
            'user_selected' => $group->isSelected()
        );
    }

    public function convertFullProduct(StoreFrontBundle\Struct\Product $product)
    {
        $data = $this->convertListProductStruct($product);

        if ($product->getUnit()) {
            $data = array_merge($data, $this->convertUnitStruct($product->getUnit()));
        }

        //set defaults for detail page combo box.
        if (!$data['maxpurchase']) {
            $data['maxpurchase'] = $this->config->get('maxPurchase');
        }
        if (!$data['purchasesteps']) {
            $data['purchasesteps'] = 1;
        }

        if ($product->getPriceGroup()) {
            $data = array_merge(
                $data,
                array(
                    'pricegroupActive' => 1,
                    'pricegroupID' => $product->getPriceGroup()->getId()
                )
            );
        }

        /**@var $first StoreFrontBundle\Struct\Product\Price */
        $first = array_shift($product->getPrices());

        $data['price'] = $this->sFormatPrice(
            $first->getCalculatedPrice()
        );

        $data['pseudoprice'] = $this->sFormatPrice(
            $first->getCalculatedPseudoPrice()
        );

        $data['pricegroup'] = $first->getCustomerGroup()->getKey();

        $data['referenceprice'] = $first->getCalculatedReferencePrice();

        if (count($product->getPrices()) > 1) {
            foreach ($product->getPrices() as $price) {
                $data['sBlockPrices'][] = $this->convertPriceStruct(
                    $price
                );
            }
        }

        //convert all product images and set cover image
        foreach ($product->getMedia() as $media) {
            $data['images'][] = $this->convertMediaStruct($media);
        }

        if (empty($data['images'])) {
            $data['image'] = $this->convertMediaStruct($product->getCover());
        } else {
            $data['image'] = array_shift($data['images']);
        }

        //convert product voting
        foreach ($product->getVotes() as $vote) {
            $data['sVoteComments'][] = $this->convertVoteStruct($vote);
        }

        if ($product->getPropertySet()) {
            $data['filtergroupID'] = $product->getPropertySet()->getId();
            $data['sProperties'] = $this->getFlatPropertyArray(
                $this->convertPropertySetStruct($product->getPropertySet())
            );
        }

        foreach ($product->getDownloads() as $download) {
            $data['sDownloads'][] = array(
                'id' => $download->getId(),
                'description' => $download->getDescription(),
                'filename' => $this->config->get('convertedBasePath') . $this->config->get('articleFiles') . "/" . $download->getFile(),
                'size' => $download->getSize()
            );
        }

        foreach ($product->getLinks() as $link) {
            $temp = array(
                'id' => $link->getId(),
                'description' => $link->getDescription(),
                'link' => $link->getLink(),
                'target' => $link->getTarget(),
                'supplierSearch' => false,
            );

            if (!preg_match("/http/", $temp['link'])) {
                $temp["link"] = "http://" . $link->getLink();
            }

            $data["sLinks"][] = $temp;
        }

        $data["sLinks"][] = array(
            'supplierSearch' => true,
            'description' => $product->getManufacturer()->getName(),
            'target' => '_parent',
            'link' => $this->getSupplierListingLink($product->getManufacturer())
        );


        $data['sRelatedArticles'] = array();
        foreach ($product->getRelatedProducts() as $relatedProduct) {
            $temp = $this->convertProductStruct($relatedProduct);

            $temp = $this->legacyEventManager->firePromotionByIdEvents($temp, null);

            $data['sRelatedArticles'][] = $temp;
        }

        $data['sSimilarArticles'] = array();
        foreach ($product->getSimilarProducts() as $similarProduct) {
            $temp = $this->convertProductStruct($similarProduct);

            $temp = $this->legacyEventManager->firePromotionByIdEvents($temp, null);

            $data['sSimilarArticles'][] = $temp;
        }

        return $data;
    }

    public function convertVoteAverageStruct(StoreFrontBundle\Struct\Product\VoteAverage $average)
    {
        $data = array(
            'averange' => round($average->getAverage()),
            'count' => $average->getCount(),
            'pointCount' => $average->getPointCount()
        );

        $data['attributes'] = $average->getAttributes();

        return $data;
    }

    public function convertVoteStruct(StoreFrontBundle\Struct\Product\Vote $vote)
    {
        $data = array(
            'id' => $vote->getId(),
            'name' => $vote->getName(),
            'headline' => $vote->getHeadline(),
            'comment' => $vote->getComment(),
            'points' => $vote->getPoints(),
            'active' => true,
            'email' => $vote->getEmail(),
            'answer' => $vote->getAnswer(),
            'datum' => '0000-00-00 00:00:00',
            'answer_date' => '0000-00-00 00:00:00'
        );

        if ($vote->getCreatedAt() instanceof \DateTime) {
            $data['datum'] = $vote->getCreatedAt()->format('Y-m-d H:i:s');
        }

        if ($vote->getAnsweredAt() instanceof \DateTime) {
            $data['answer_date'] = $vote->getAnsweredAt()->format('Y-m-d H:i:s');
        }

        $data['attributes'] = $vote->getAttributes();

        return $data;
    }

    public function convertPriceStruct(StoreFrontBundle\Struct\Product\Price $price)
    {
        $data = array(
            'valFrom' => $price->getFrom(),
            'valTo' => $price->getTo(),
            'from' => $price->getFrom(),
            'to' => $price->getTo(),
            'price' => $price->getCalculatedPrice(),
            'pseudoprice' => $price->getCalculatedPseudoPrice(),
            'referenceprice' => $price->getCalculatedReferencePrice()
        );

        $data['attributes'] = $price->getAttributes();

        return $data;
    }

    public function convertMediaStruct(StoreFrontBundle\Struct\Media $media)
    {
        //now we get the configured image and thumbnail dir.
        $imageDir = $this->config->get('convertedBasePath') . '/media/image/';
        $imageDir = str_replace('/media/image/', DIRECTORY_SEPARATOR, $imageDir);

        $src = $media->getThumbnails();
        foreach ($src as &$thumbnail) {
            $thumbnail = $imageDir . $thumbnail;
        }

        $src['original'] = $imageDir . $media->getFile();

        $data = array(
            'id' => $media->getId(),
            'position' => 1,
            'extension' => $media->getExtension(),
            'main' => $media->getPreview(),
            'parentId' => null,
            'src' => $src,
            'res' => array(
                'original' => array(
                    'width' => 0,
                    'height' => 0,
                ),
                'description' => $media->getDescription(),
            )
        );

        $data['attributes'] = $media->getAttributes();

        return $data;
    }

    public function convertUnitStruct(StoreFrontBundle\Struct\Product\Unit $unit)
    {
        $data = array(
            'minpurchase' => $unit->getMinPurchase(),
            'maxpurchase' => $unit->getMaxPurchase(),
            'purchasesteps' => $unit->getPurchaseStep(),
            'purchaseunit' => $unit->getPurchaseUnit(),
            'referenceunit' => $unit->getReferenceUnit(),
            'packunit' => $unit->getPackUnit(),
            'unitID' => $unit->getId(),
            'sUnit' => array(
                'unit' => $unit->getUnit(),
                'description' => $unit->getName()
            )
        );

        $data['unit_attributes'] = $unit->getAttributes();

        return $data;
    }

    public function getSupplierListingLink(StoreFrontBundle\Struct\Product\Manufacturer $manufacturer)
    {
        return $this->config->get('baseFile') .
        "sViewport=supplier&sSupplier=" . $manufacturer->getId() .
        "&sSearchText=" . urlencode($manufacturer->getName());
    }

    /**
     * Converts the passed ListProduct struct to a shopware 3-4 array structure.
     *
     * @param StoreFrontBundle\Struct\ListProduct $product
     * @return array
     */
    public function convertProductStruct(StoreFrontBundle\Struct\ListProduct $product)
    {
        if (!$product instanceof StoreFrontBundle\Struct\ListProduct) {
            return array();
        }

        //required for backward compatibility
        if (!$product->getCheapestPrice()) {
            $cheapestPrice = $product->getPrices();
            $cheapestPrice = array_shift($cheapestPrice);
        } else {
            $cheapestPrice = $product->getCheapestPrice();
        }

        $unit = $cheapestPrice->getUnit();

        $price = $this->sFormatPrice(
            $cheapestPrice->getCalculatedPrice()
        );

        $pseudoPrice = $this->sFormatPrice(
            $cheapestPrice->getCalculatedPseudoPrice()
        );

        $referencePrice = $this->sFormatPrice(
            $cheapestPrice->getCalculatedReferencePrice()
        );

        $promotion = $this->convertListProductStruct($product);

        $promotion = array_merge(
            $promotion,
            array(
                'price' => $price,
                'pseudoprice' => $pseudoPrice,
                'pricegroup' => $cheapestPrice->getCustomerGroup()->getKey(),
            )
        );

        if ($referencePrice) {
            $promotion['referenceprice'] = $referencePrice;
        }

        if ($product->getPriceGroup()) {
            $promotion['pricegroupActive'] = true;
            $promotion['pricegroupID'] = $product->getPriceGroup()->getId();
        }

        if (count($product->getPrices()) > 1) {
            $promotion['priceStartingFrom'] = $price;
        }

        if ($cheapestPrice->getCalculatedPseudoPrice()) {
            $discPseudo = $cheapestPrice->getCalculatedPseudoPrice();
            $discPrice = $cheapestPrice->getCalculatedPrice();

            $discount = round(($discPrice / $discPseudo * 100) - 100, 2) * -1;
            $promotion["pseudopricePercent"] = array(
                "int" => round($discount, 0),
                "float" => $discount
            );
        }

        if ($unit) {
            $promotion = array_merge($promotion, $this->convertUnitStruct($unit));
        }

        if ($product->getCover()) {
            $promotion['image'] = $this->convertMediaStruct($product->getCover());
        }

        $promotion["linkBasket"] = $this->config->get('baseFile') .
            "?sViewport=basket&sAdd=" . $promotion["ordernumber"];

        $promotion["linkDetails"] = $this->config->get('baseFile') .
            "?sViewport=detail&sArticle=" . $promotion["articleID"];

        return $promotion;
    }

    public function convertListProductStruct(StoreFrontBundle\Struct\ListProduct $product)
    {
        $data = array(
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
            'weight' => $product->getWeight(),
            'shippingtime' => $product->getShippingTime(),
            'pricegroupActive' => false,
            'pricegroupID' => null,
            'length' => $product->getLength(),
            'height' => $product->getHeight(),
            'width' => $product->getWidth(),
            'laststock' => $product->isCloseouts(),
            'additionaltext' => $product->getAdditional(),
            'datum' => $product->getCreatedAt(),
            'sales' => $product->getSales(),
            'filtergroupID' => null,
            'priceStartingFrom' => null,
            'pseudopricePercent' => null,
            //flag inside mini product
            'sVariantArticle' => null,
            'sConfigurator' => $product->hasConfigurator(),
            //only used for full products
            'metaTitle' => $product->getMetaTitle(),
            'shippingfree' => $product->isShippingFree(),
            'suppliernumber' => $product->getManufacturerNumber(),
            'notification' => $product->allowsNotification(),
            'ean' => $product->getEan(),
            'keywords' => $product->getKeywords(),
            'sReleasedate' => $product->getReleaseDate(),
        );

        if ($product->hasAttribute('core')) {
            $data = array_merge($data, $product->getAttribute('core')->toArray());
        }

        $data['attributes'] = $product->getAttributes();

        if ($product->getManufacturer()) {
            $data = array_merge(
                $data,
                array(
                    'supplierName' => $product->getManufacturer()->getName(),
                    'supplierImg' => $product->getManufacturer()->getCoverFile(),
                    'supplierID' => $product->getManufacturer()->getId(),
                    'supplierDescription' => $product->getManufacturer()->getDescription(),
                )
            );

            $data['supplier_attributes'] = $product->getManufacturer()->getAttributes();
        }

        if ($product->hasAttribute('marketing')) {
            /**@var $marketing StoreFrontBundle\Struct\Product\MarketingAttribute */
            $marketing = $product->getAttribute('marketing');
            $promotion['newArticle'] = $marketing->isNew();
            $promotion['sUpcoming'] = $marketing->comingSoon();
            $promotion['topseller'] = $marketing->isTopSeller();
        }

        $today = new \DateTime();
        if ($product->getReleaseDate() && $product->getReleaseDate() > $today) {
            $promotion['sReleasedate'] = $product->getReleaseDate()->format('Y-m-d');
        }

        return $data;
    }

    public function getFlatPropertyArray(array $propertySet)
    {
        $data = array();
        foreach ($propertySet['groups'] as $group) {
            $groupData = array(
                'id' => $group['id'],
                'optionID' => $group['id'],
                'name' => $group['name'],
                'groupID' => $propertySet['id'],
                'groupName' => $propertySet['name'],
                'values' => array(),
                'attributes' => $group['attributes']
            );

            $options = array();
            foreach ($group['options'] as $option) {
                $options[] = array(
                    'id' => $option['id'],
                    'name' => $option['name'],
                    'attributes' => $option['attributes']
                );
            }

            $groupData['values'] = array_column($options, 'name');
            $groupData['value'] = implode(', ', $groupData['values']);

            $first = array_shift($options);
            $groupData['valueID'] = $first['id'];

            $data[$groupData['id']] = $groupData;
        }
        return $data;
    }

    public function convertPropertySetStruct(StoreFrontBundle\Struct\Property\Set $set)
    {
        $data = array(
            'id' => $set->getId(),
            'name' => $set->getName(),
            'isComparable' => $set->isComparable(),
            'groups' => array(),
            'attributes' => array()
        );

        foreach ($set->getAttributes() as $key => $attribute) {
            $data['attributes'][$key] = $attribute->toArray();
        }

        foreach ($set->getGroups() as $group) {
            $data['groups'][] = $this->convertPropertyGroupStruct($group);
        }

        return $data;
    }

    public function convertPropertyGroupStruct(StoreFrontBundle\Struct\Property\Group $group)
    {
        $data = array(
            'id' => $group->getId(),
            'name' => $group->getName(),
            'isFilterable' => $group->isFilterable(),
            'options' => array(),
            'attributes' => array()
        );

        foreach ($group->getAttributes() as $key => $attribute) {
            $data['attributes'][$key] = $attribute->toArray();
        }

        foreach ($group->getOptions() as $option) {
            $data['options'][] = $this->convertPropertyOptionStruct($option);
        }

        return $data;
    }

    public function convertPropertyOptionStruct(StoreFrontBundle\Struct\Property\Option $option)
    {
        $data = array(
            'id' => $option->getId(),
            'name' => $option->getName(),
            'attributes' => array()
        );

        foreach ($option->getAttributes() as $key => $attribute) {
            $data['attributes'][$key] = $attribute->toArray();
        }

        return $data;
    }

    public function convertManufacturerStruct(StoreFrontBundle\Struct\Product\Manufacturer $manufacturer)
    {
        $data = array(
            'id' => $manufacturer->getId(),
            'name' => $manufacturer->getName(),
            'description' => $manufacturer->getDescription(),
            'metaTitle' => $manufacturer->getMetaTitle(),
            'metaDescription' => $manufacturer->getMetaDescription(),
            'metaKeywords' => $manufacturer->getMetaKeywords(),
            'link' => $manufacturer->getLink(),
            'image' => $manufacturer->getCoverFile(),
        );

        $data['attribute'] = array();
        foreach ($manufacturer->getAttributes() as $attribute) {
            $data['attribute'] = array_merge(
                $data['attribute'],
                $attribute->toArray()
            );
        }

        return $data;
    }

    public function convertConfiguratorStruct(
        StoreFrontBundle\Struct\ListProduct $product,
        StoreFrontBundle\Struct\Configurator\Set $set
    ) {
        $groups = array();
        foreach ($set->getGroups() as $group) {
            $groupData = $this->convertConfiguratorGroupStruct($group);

            $options = array();
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

        $data = array(
            'sConfigurator' => $groups,
            'sConfiguratorSettings' => $settings
        );

        return $data;
    }


    /**
     * Creates the settings array for the passed configurator set
     *
     * @param StoreFrontBundle\Struct\Configurator\Set $set
     * @param StoreFrontBundle\Struct\Product $product
     * @return array
     */
    public function getConfiguratorSettings(
        StoreFrontBundle\Struct\Configurator\Set $set,
        StoreFrontBundle\Struct\Product $product
    ) {
        $settings = array(
            'instock' => $product->isCloseouts(),
            'articleID' => $product->getId(),
            'type' => $set->getType()
        );

        //switch the template for the different configurator types.
        if ($set->getType() == 1) {
            //Selection configurator
            $settings["template"] = "article_config_step.tpl";

        } elseif ($set->getType() == 2) {
            //Table configurator
            $settings["template"] = "article_config_picture.tpl";

        } else {
            //Other configurator types
            $settings["template"] = "article_config_upprice.tpl";
        }

        return $settings;
    }

    /**
     * Converts a configurator option struct which used for default or selection configurators.
     *
     * @param StoreFrontBundle\Struct\Configurator\Group $group
     * @param StoreFrontBundle\Struct\Configurator\Option $option
     * @return array
     */
    public function convertConfiguratorOptionStruct(
        StoreFrontBundle\Struct\Configurator\Group $group,
        StoreFrontBundle\Struct\Configurator\Option $option
    ) {
        $data = array(
            'optionID' => $option->getId(),
            'groupID' => $group->getId(),
            'optionname' => $option->getName(),
            'user_selected' => $option->isSelected(),
            'selected' => $option->isSelected()
        );

        if ($option->getMedia()) {
            $data['media'] = $this->convertMediaStruct($option->getMedia());
        }

        return $data;
    }

    /**
     * Formats article prices
     * @access public
     * @param float $price
     * @return float price
     */
    private function sFormatPrice($price)
    {
        $price = str_replace(",", ".", $price);
        $price = $this->sRound($price);
        $price = str_replace(".", ",", $price); // Replaces points with commas
        $commaPos = strpos($price, ",");
        if ($commaPos) {

            $part = substr($price, $commaPos + 1, strlen($price) - $commaPos);
            switch (strlen($part)) {
                case 1:
                    $price .= "0";
                    break;
                case 2:
                    break;
            }
        } else {
            if (!$price) {
                $price = "0";
            } else {
                $price .= ",00";
            }
        }

        return $price;
    }

    private function sRound($moneyfloat = null)
    {
        $money_str = explode(".", $moneyfloat);
        if (empty($money_str[1])) {
            $money_str[1] = 0;
        }
        $money_str[1] = substr($money_str[1], 0, 3); // convert to rounded (to the nearest thousandth) string

        $money_str = $money_str[0] . "." . $money_str[1];

        return round($money_str, 2);
    }
}