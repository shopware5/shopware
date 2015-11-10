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

namespace Shopware\Components\Compatibility;

use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;

/**
 * @category  Shopware
 * @package   Shopware\Components\Compatibility
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class LegacyEventManager
{
    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @param \Enlight_Event_EventManager $eventManager
     * @param \Shopware_Components_Config $config
     * @param ContextServiceInterface $contextService
     */
    public function __construct(
        \Enlight_Event_EventManager $eventManager,
        \Shopware_Components_Config $config,
        ContextServiceInterface $contextService
    ) {
        $this->eventManager = $eventManager;
        $this->config = $config;
        $this->contextService = $contextService;
    }

    /**
     * Following events are deprecated and only implemented for backward compatibility to shopware 4
     * Removed with shopware 5.1
     *
     * @param array $result
     * @param $categoryId
     * @param \sArticles $module
     * @return mixed
     */
    public function fireArticlesByCategoryEvents(
        array $result,
        $categoryId,
        \sArticles $module
    ) {
        foreach ($result['sArticles'] as &$article) {
            $cheapestPrice = $this->eventManager->filter(
                'sArticles::sGetCheapestPrice::replace',
                $article["price"],
                array(
                    'subject' => $module,
                    'article' => $article['articleID'],
                    'group' => $article["pricegroup"],
                    'pricegroup' => $article["pricegroupID"],
                    'usepricegroups' => $article["pricegroupActive"],
                    'realtime' => false,
                    'returnArrayIfConfigurator' => true
                )
            );

            // Reformat the price for further processing
            if (!is_array($cheapestPrice)) {
                $cheapestPrice = array(
                    $cheapestPrice,
                    0
                );
            }

            // Temporary save default price
            $article["priceDefault"] = $article["price"];

            // Display always the cheapest price
            if (!empty($cheapestPrice[0])) {
                $article["price"] = $cheapestPrice[0];
            }

            $article["price"] = $this->eventManager->filter(
                'sArticles::sCalculatingPrice::replace',
                $article["price"],
                array(
                    'subject' => $module,
                    'price' => $article["price"],
                    'tax' => $article["tax"],
                    'taxId' => $article["taxID"],
                    'article' => $article
                )
            );

            if (!empty($article["pseudoprice"])) {
                $article["pseudoprice"] = $this->eventManager->filter(
                    'sArticles::sCalculatingPrice::replace',
                    $article["pseudoprice"],
                    array(
                        'subject' => $module,
                        'price' => $article["pseudoprice"],
                        'tax' => $article["tax"],
                        'taxId' => $article["taxID"],
                        'article' => $article
                    )
                );
            }

            $article["image"] = $this->eventManager->filter(
                'sArticles::getArticleListingCover::replace',
                $article["image"],
                array(
                    'subject' => $module,
                    'articleId' => $article['articleID'],
                    'forceMainImage' => $this->config->get('forceArticleMainImageInListing')
                )
            );

            $calculatedBasePriceData = $this->eventManager->filter(
                'sArticles::calculateCheapestBasePriceData::replace',
                null,
                array(
                    'price' => $article["price"],
                    'articleId' => $article["articleID"],
                    'priceGroup' => $article["pricegroup"],
                    'priceGroupId' => $article["pricegroupID"]
                )
            );

            if (!empty($calculatedBasePriceData)) {
                $article["purchaseunit"] = empty($calculatedBasePriceData["purchaseunit"]) ? null: $calculatedBasePriceData["purchaseunit"];
                $article["referenceunit"] = empty($calculatedBasePriceData["referenceunit"]) ? null: $calculatedBasePriceData["referenceunit"];
                $article["sUnit"] = empty($calculatedBasePriceData["sUnit"]) ? null: $calculatedBasePriceData["sUnit"];
                $article['referenceprice'] = empty($calculatedBasePriceData["referenceprice"]) ? null: $calculatedBasePriceData["referenceprice"];
            }

            $article = Enlight()->Events()->filter(
                'Shopware_Modules_Articles_sGetArticlesByCategory_FilterLoopEnd',
                $article,
                array(
                    'subject' => $module,
                    'id' => $categoryId
                )
            );
        }

        return $this->eventManager->filter(
            'Shopware_Modules_Articles_sGetArticlesByCategory_FilterResult',
            $result,
            array(
                'subject' => $module,
                'id' => $categoryId
            )
        );
    }

    /**
     * Following events are deprecated and only implemented for backward compatibility to shopware 4
     * Removed with shopware 5.1
     *
     * @param array $result
     * @param $category
     * @param \sArticles $module
     * @return array
     */
    public function firePromotionByIdEvents($result, $category, \sArticles $module)
    {
        $articleId = $result['articleID'];

        $result["sProperties"] = $this->eventManager->filter(
            'sArticles::sGetArticleProperties::replace',
            (isset($result["sProperties"])) ? $result["sProperties"] : [],
            array(
                'subject'  => $module,
                'articleId' => $articleId,
                'filterGroupId' => $result['filtergroupID']
            )
        );

        $result["sProperties"] = $this->eventManager->filter(
            'sArticles::sGetArticleProperties::after',
            $result["sProperties"],
            array(
                'subject' => $module,
                'articleId' => $articleId,
                'filterGroupId' => $result['filtergroupID']
            )
        );

        $cheapestPrice = $this->eventManager->filter(
            'sArticles::sGetCheapestPrice::replace',
            $result['priceStartingFrom'],
            array(
                'subject' => $module,
                'article' => $articleId,
                'group' => $result["pricegroup"],
                'pricegroup' => $result["pricegroupID"],
                'usepricegroups' => $result["pricegroupActive"],
            )
        );

        $cheapestPrice = $this->eventManager->filter(
            'sArticles::sGetCheapestPrice::after',
            $cheapestPrice,
            array(
                'subject' => $module,
                'article' => $articleId,
                'group' => $result["pricegroup"],
                'pricegroup' => $result["pricegroupID"],
                'usepricegroups' => $result["pricegroupActive"],
            )
        );

        if (!is_array($cheapestPrice)) {
            $cheapestPrice = array(
                $cheapestPrice,
                0
            );
        }
        $result['priceStartingFrom'] = $cheapestPrice[0];

        $result["image"] = $this->eventManager->filter(
            'sArticles::getArticleListingCover::replace',
            $result["image"],
            array(
                'subject' => $module,
                'articleId' => $articleId,
                'forceMainImage' => $this->config->get('forceArticleMainImageInListing')
            )
        );

        $result["image"] = $this->eventManager->filter(
            'sArticles::sGetArticlePictures::replace',
            $result["image"],
            array(
                'subject' => $module,
                'sArticleID' => $articleId,
                'onlyCover' => true,
                'pictureSize' => 0,
                'ordernumber' => null,
                'allImages' => null,
                'realtime' => null,
                'forceMainImage' => $this->config->get('forceArticleMainImageInListing')
            )
        );

        $result["image"] = $this->eventManager->filter(
            'sArticles::sGetArticlePictures::after',
            $result["image"],
            array(
                'subject' => $module,
                'sArticleID' => $articleId,
                'onlyCover' => true,
                'pictureSize' => 0,
                'ordernumber' => null,
                'allImages' => null,
                'realtime' => null,
                'forceMainImage' => $this->config->get('forceArticleMainImageInListing')
            )
        );

        $result["image"] = $this->eventManager->filter(
            'sArticles::getArticleListingCover::after',
            $result["image"],
            array(
                'subject' => $module,
                'articleId' => $articleId,
                'forceMainImage' => $this->config->get('forceArticleMainImageInListing')
            )
        );

        $result["priceStartingFrom"] = $this->eventManager->filter(
            'sArticles::sCalculatingPrice::replace',
            $result["priceStartingFrom"],
            array(
                'subject' => $module,
                'price' => $result["priceStartingFrom"],
                'tax' => $result["tax"],
                'taxId' => $result["taxID"],
                'article' => $result
            )
        );

        $result["priceStartingFrom"] = $this->eventManager->filter(
            'sArticles::sCalculatingPrice::after',
            $result["priceStartingFrom"],
            array(
                'subject' => $module,
                'price' => $result["priceStartingFrom"],
                'tax' => $result["tax"],
                'taxId' => $result["taxID"],
                'article' => $result
            )
        );

        $result["price"] = $this->eventManager->filter(
            'sArticles::sCalculatingPrice::replace',
            $result["price"],
            array(
                'subject' => $module,
                'price' => $result["price"],
                'tax' => $result["tax"],
                'taxId' => $result["taxID"],
                'article' => $result
            )
        );

        $result["price"] = $this->eventManager->filter(
            'sArticles::sCalculatingPrice::after',
            $result["price"],
            array(
                'subject' => $module,
                'price' => $result["price"],
                'tax' => $result["tax"],
                'taxId' => $result["taxID"],
                'article' => $result
            )
        );

        if ($result["pseudoprice"]) {
            $result["pseudoprice"] = $this->eventManager->filter(
                'sArticles::sCalculatingPrice::replace',
                $result["pseudoprice"],
                array(
                    'subject' => $module,
                    'price' => $result["pseudoprice"],
                    'tax' => $result["tax"],
                    'taxId' => $result["taxID"],
                    'article' => $result
                )
            );

            $result["pseudoprice"] = $this->eventManager->filter(
                'sArticles::sCalculatingPrice::after',
                $result["pseudoprice"],
                array(
                    'subject' => $module,
                    'price' => $result["pseudoprice"],
                    'tax' => $result["tax"],
                    'taxId' => $result["taxID"],
                    'article' => $result
                )
            );
        }

        $calculatedBasePriceData = $this->eventManager->filter(
            'sArticles::calculateCheapestBasePriceData::replace',
            null,
            array(
                'price' => $result["price"],
                'articleId' => $articleId,
                'priceGroup' => $result["pricegroup"],
                'priceGroupId' => $result["pricegroupID"]
            )
        );

        $calculatedBasePriceData = $this->eventManager->filter(
            'sArticles::calculateCheapestBasePriceData::after',
            $calculatedBasePriceData,
            array(
                'price' => $result["price"],
                'articleId' => $articleId,
                'priceGroup' => $result["pricegroup"],
                'priceGroupId' => $result["pricegroupID"]
            )
        );

        if (!empty($calculatedBasePriceData)) {
            $result["purchaseunit"] = empty($calculatedBasePriceData["purchaseunit"]) ? null: $calculatedBasePriceData["purchaseunit"];
            $result["referenceunit"] = empty($calculatedBasePriceData["referenceunit"]) ? null: $calculatedBasePriceData["referenceunit"];
            $result["sUnit"] = empty($calculatedBasePriceData["sUnit"]) ? null: $calculatedBasePriceData["sUnit"];
            $result['referenceprice'] = empty($calculatedBasePriceData["referenceprice"]) ? null: $calculatedBasePriceData["referenceprice"];
        }

        return Enlight()->Events()->filter(
            'Shopware_Modules_Articles_GetPromotionById_FilterResult',
            $result,
            array(
                'subject' => $module,
                'mode' => 'fix',
                'category' => $category,
                'value' => $result['articleID']
            )
        );
    }

    /**
     * Following events are deprecated and only implemented for backward compatibility to shopware 4
     * Removed with shopware 5.1
     *
     * @param array $product
     * @param \sArticles $module
     * @return array|mixed
     */
    public function fireArticleByIdEvents(array $product, \sArticles $module)
    {
        $getArticle = $product;
        $context = $this->contextService->getShopContext();

        if ($getArticle["pricegroupActive"]) {
            $getArticle["priceBeforePriceGroup"] = $getArticle["price"];

            $getArticle["price"] = $this->eventManager->filter(
                'sArticles::sGetPricegroupDiscount::replace',
                $getArticle["price"],
                array(
                    'subject' => $module,
                    'customergroup' => $context->getCurrentCustomerGroup()->getKey(),
                    'groupID' => $getArticle["pricegroupID"],
                    'listprice' => $getArticle["price"],
                    'quantity' => 1,
                    'doMatrix' => false
                )
            );

            $getArticle["price"] = $this->eventManager->filter(
                'sArticles::sGetPricegroupDiscount::after',
                $getArticle["price"],
                array(
                    'subject' => $module,
                    'customergroup' => $context->getCurrentCustomerGroup()->getKey(),
                    'groupID' => $getArticle["pricegroupID"],
                    'listprice' => $getArticle["price"],
                    'quantity' => 1,
                    'doMatrix' => false
                )
            );


            $getArticle["sBlockPrices"] = $this->eventManager->filter(
                'sArticles::sGetPricegroupDiscount::replace',
                $getArticle["sBlockPrices"],
                array(
                    'subject' => $module,
                    'customergroup' => $context->getCurrentCustomerGroup()->getKey(),
                    'groupID' => $getArticle["pricegroupID"],
                    'listprice' => $getArticle["price"],
                    'quantity' => 1,
                    'doMatrix' => true
                )
            );

            $getArticle["sBlockPrices"] = $this->eventManager->filter(
                'sArticles::sGetPricegroupDiscount::after',
                $getArticle["sBlockPrices"],
                array(
                    'subject' => $module,
                    'customergroup' => $context->getCurrentCustomerGroup()->getKey(),
                    'groupID' => $getArticle["pricegroupID"],
                    'listprice' => $getArticle["price"],
                    'quantity' => 1,
                    'doMatrix' => true
                )
            );
        } else {
            foreach ($getArticle["sBlockPrices"] as &$blockPrice) {
                $blockPrice["price"] = $this->eventManager->filter(
                    'sArticles::sCalculatingPrice::replace',
                    $blockPrice["price"],
                    array(
                        'subject' => $module,
                        'price' => $blockPrice["price"],
                        'tax' => $getArticle["tax"],
                        'taxId' => $getArticle["taxID"],
                        'article' => $getArticle
                    )
                );
                $blockPrice["price"] = $this->eventManager->filter(
                    'sArticles::sCalculatingPrice::after',
                    $blockPrice["price"],
                    array(
                        'subject' => $module,
                        'price' => $blockPrice["price"],
                        'tax' => $getArticle["tax"],
                        'taxId' => $getArticle["taxID"],
                        'article' => $getArticle
                    )
                );

                if (!$blockPrice['pseudoprice']) {
                    continue;
                }

                $blockPrice["pseudoprice"] = $this->eventManager->filter(
                    'sArticles::sCalculatingPrice::replace',
                    $blockPrice["pseudoprice"],
                    array(
                        'subject' => $module,
                        'price' => $blockPrice["pseudoprice"],
                        'tax' => $getArticle["tax"],
                        'taxId' => $getArticle["taxID"],
                        'article' => $getArticle
                    )
                );

                $blockPrice["pseudoprice"] = $this->eventManager->filter(
                    'sArticles::sCalculatingPrice::after',
                    $blockPrice["pseudoprice"],
                    array(
                        'subject' => $module,
                        'price' => $blockPrice["pseudoprice"],
                        'tax' => $getArticle["tax"],
                        'taxId' => $getArticle["taxID"],
                        'article' => $getArticle
                    )
                );
            }
        }

        $getArticle = Enlight()->Events()->filter(
            'Shopware_Modules_Articles_GetArticleById_FilterArticle',
            $getArticle,
            array(
                'subject' => $module,
                'id' => $product['articleID'],
                'customergroup' => $context->getCurrentCustomerGroup()->getKey()
            )
        );

        if ($getArticle["unitID"]) {
            $getArticle["sUnit"] = $this->eventManager->filter(
                'sArticles::sGetUnit::replace',
                $getArticle["sUnit"],
                array(
                    'subject' => $module,
                    'id' => $getArticle["unitID"]
                )
            );
            $getArticle["sUnit"] = $this->eventManager->filter(
                'sArticles::sGetUnit::after',
                $getArticle["sUnit"],
                array(
                    'subject' => $module,
                    'id' => $getArticle["unitID"]
                )
            );
        }

        // Get cheapest price
        $getArticle["priceStartingFrom"] = $this->eventManager->filter(
            'sArticles::sGetCheapestPrice::replace',
            $getArticle["priceStartingFrom"],
            array(
                'subject' => $module,
                'article' => $getArticle["articleID"],
                'group' => $getArticle["pricegroup"],
                'pricegroup' => $getArticle["pricegroupID"],
                'usepricegroups' => $getArticle["pricegroupActive"]
            )
        );

        // Get cheapest price
        $getArticle["priceStartingFrom"] = $this->eventManager->filter(
            'sArticles::sGetCheapestPrice::after',
            $getArticle["priceStartingFrom"],
            array(
                'subject' => $module,
                'article' => $getArticle["articleID"],
                'group' => $getArticle["pricegroup"],
                'pricegroup' => $getArticle["pricegroupID"],
                'usepricegroups' => $getArticle["pricegroupActive"]
            )
        ) ;

        if ($getArticle["price"]) {
            $getArticle["price"] = $this->eventManager->filter(
                'sArticles::sCalculatingPrice::replace',
                $getArticle["price"],
                array(
                    'subject' => $module,
                    'price' => $getArticle["price"],
                    'tax' => $getArticle["tax"],
                    'taxId' => $getArticle["taxID"],
                    'article' => $getArticle
                )
            );
            $getArticle["price"] = $this->eventManager->filter(
                'sArticles::sCalculatingPrice::after',
                $getArticle["price"],
                array(
                    'subject' => $module,
                    'price' => $getArticle["price"],
                    'tax' => $getArticle["tax"],
                    'taxId' => $getArticle["taxID"],
                    'article' => $getArticle
                )
            );
        }

        $getArticle["image"] = $this->eventManager->filter(
            'sArticles::sGetArticlePictures::replace',
            $getArticle["image"],
            array(
                'subject' => $module,
                'sArticleID'  => $getArticle["articleID"],
                'onlyCover'   => true,
                'pictureSize' => 4,
                'ordernumber' => $getArticle['ordernumber']
            )
        );
        $getArticle["image"] = $this->eventManager->filter(
            'sArticles::sGetArticlePictures::after',
            $getArticle["image"],
            array(
                'subject' => $module,
                'sArticleID'  => $getArticle["articleID"],
                'onlyCover'   => true,
                'pictureSize' => 4,
                'ordernumber' => $getArticle['ordernumber']
            )
        );

        $getArticle["images"] = $this->eventManager->filter(
            'sArticles::sGetArticlePictures::replace',
            $getArticle["images"],
            array(
                'subject' => $module,
                'sArticleID'  => $getArticle["articleID"],
                'onlyCover'   => false,
                'pictureSize' => 0,
                'ordernumber' => $getArticle['ordernumber']
            )
        );
        $getArticle["images"] = $this->eventManager->filter(
            'sArticles::sGetArticlePictures::after',
            $getArticle["images"],
            array(
                'subject' => $module,
                'sArticleID'  => $getArticle["articleID"],
                'onlyCover'   => false,
                'pictureSize' => 0,
                'ordernumber' => $getArticle['ordernumber']
            )
        );

        $getArticle["sVoteAverange"] = $this->eventManager->filter(
            'sArticles::sGetArticlesAverangeVote::replace',
            $getArticle["sVoteAverange"],
            array(
                'subject' => $module,
                'article' => $getArticle["articleID"]
            )
        );

        $getArticle["sVoteAverange"] = $this->eventManager->filter(
            'sArticles::sGetArticlesAverangeVote::after',
            $getArticle["sVoteAverange"],
            array(
                'subject' => $module,
                'article' => $getArticle["articleID"]
            )
        );

        $getArticle["sVoteComments"] = $this->eventManager->filter(
            'sArticles::sGetArticlesVotes::replace',
            $getArticle["sVoteComments"],
            array(
                'subject' => $module,
                'article' => $getArticle["articleID"]
            )
        );

        $getArticle["sVoteComments"] = $this->eventManager->filter(
            'sArticles::sGetArticlesVotes::after',
            $getArticle["sVoteComments"],
            array(
                'subject' => $module,
                'article' => $getArticle["articleID"]
            )
        );

        if (!empty($getArticle["filtergroupID"])) {
            $getArticle["sProperties"] = $this->eventManager->filter(
                'sArticles::sGetArticleProperties::replace',
                $getArticle["sProperties"],
                array(
                    'subject' => $module,
                    'articleId' => $getArticle["articleID"],
                    'filterGroupId' => $getArticle["filtergroupID"]
                )
            );
            $getArticle["sProperties"] = $this->eventManager->filter(
                'sArticles::sGetArticleProperties::after',
                $getArticle["sProperties"],
                array(
                    'subject' => $module,
                    'articleId' => $getArticle["articleID"],
                    'filterGroupId' => $getArticle["filtergroupID"]
                )
            );
        }

        $getArticle = Enlight()->Events()->filter(
            'Shopware_Modules_Articles_GetArticleById_FilterResult',
            $getArticle,
            array(
                'subject' => $module,
                'id' => $getArticle["articleID"],
                'isBlog' => false,
                'customergroup' => $context->getCurrentCustomerGroup()->getKey()
            )
        );

        return $getArticle;
    }
}
