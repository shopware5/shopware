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

use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\DependencyInjection\Container;

class sArticlesComparisons implements \Enlight_Hook
{
    /**
     * @var sArticles
     */
    private $articleModule;

    /**
     * @var \sSystem
     */
    private $systemModule;

    /**
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private $db;

    /**
     * @var Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    public function __construct(sArticles $articleModule, Container $container)
    {
        $this->articleModule = $articleModule;
        $this->systemModule = $articleModule->sSYSTEM;

        $this->db = $container->get('db');
        $this->config = $container->get('config');
        $this->session = $container->get('session');
        $this->contextService = $container->get('shopware_storefront.context_service');
    }

    /**
     * Delete products from comparision chart
     *
     * @param int $articleId Unique product id - refers to s_articles.id
     */
    public function sDeleteComparison($articleId)
    {
        $productId = (int) $articleId;
        if ($productId) {
            $this->db->executeUpdate(
                'DELETE FROM s_order_comparisons WHERE sessionID=? AND articleID=?',
                [$this->session->offsetGet('sessionId'), $productId]
            );
        }
    }

    /**
     * Delete all products from comparision chart
     */
    public function sDeleteComparisons()
    {
        $sql = '
          DELETE FROM s_order_comparisons WHERE sessionID=?
        ';

        $this->db->executeUpdate($sql, [$this->session->offsetGet('sessionId')]);
    }

    /**
     * Insert products in comparision chart
     *
     * @param int $articleId s_articles.id
     *
     * @throws Enlight_Exception
     *
     * @return bool|string|null true/false
     */
    public function sAddComparison($articleId)
    {
        $productId = (int) $articleId;
        if (!$productId) {
            return null;
        }

        // Check if this product is already noted
        $checkForProduct = $this->db->fetchRow(
            'SELECT id FROM s_order_comparisons WHERE sessionID=? AND articleID=?',
            [$this->session->offsetGet('sessionId'), $productId]
        );

        // Check if max. numbers of products for one comparison-session is reached
        $checkNumberProducts = $this->db->fetchRow(
            'SELECT COUNT(id) AS countArticles FROM s_order_comparisons WHERE sessionID=?',
            [$this->session->offsetGet('sessionId')]
        );

        if ($checkNumberProducts['countArticles'] >= $this->config->offsetGet('sMAXCOMPARISONS')) {
            return 'max_reached';
        }

        if (!$checkForProduct['id']) {
            $productName = $this->db->fetchOne(
                'SELECT s_articles.name AS articleName FROM s_articles WHERE id = ?',
                [$productId]
            );

            if (!$productName) {
                return false;
            }

            $sql = '
            INSERT INTO s_order_comparisons (sessionID, userID, articlename, articleID, datum)
            VALUES (?,?,?,?,now())
            ';

            $queryNewPrice = $this->db->executeUpdate($sql, [
                $this->session->offsetGet('sessionId'),
                empty($this->session['sUserId']) ? 0 : $this->session['sUserId'],
                $productName,
                $productId,
            ]);

            if (!$queryNewPrice) {
                throw new Enlight_Exception('sArticles##sAddComparison##01: Error in SQL-query');
            }
        }

        return true;
    }

    /**
     * Get all products from comparision chart
     *
     * @return array Associative array with all products or empty array
     */
    public function sGetComparisons()
    {
        if (!$this->session->offsetGet('sessionId')) {
            return [];
        }

        // Get all comparisons for this user
        $checkForProduct = $this->db->fetchAll(
            'SELECT * FROM s_order_comparisons WHERE sessionID=?',
            [$this->session->offsetGet('sessionId')]
        );

        if (!count($checkForProduct)) {
            return [];
        }

        foreach ($checkForProduct as $k => $product) {
            $checkForProduct[$k]['articlename'] = stripslashes($product['articlename']);
            $checkForProduct[$k] = $this->articleModule->sGetTranslation($product, $product['articleID'], 'article');
            if (!empty($checkForProduct[$k]['articleName'])) {
                $checkForProduct[$k]['articlename'] = $checkForProduct[$k]['articleName'];
            }

            $checkForProduct[$k]['articleId'] = $product['articleID'];
        }

        return $checkForProduct;
    }

    /**
     * Get all products and a table of their properties as an array
     *
     * @return array Associative array with all products or empty array
     */
    public function sGetComparisonList()
    {
        if (!$this->session->offsetGet('sessionId')) {
            return [];
        }

        $products = [];

        // Get all comparisons for this user
        $checkForProduct = $this->db->fetchAll(
            'SELECT * FROM s_order_comparisons WHERE sessionID=?',
            [$this->session->offsetGet('sessionId')]
        );

        if (!count($checkForProduct)) {
            return [];
        }

        foreach ($checkForProduct as $product) {
            if ($product['articleID']) {
                $promotion = $this->articleModule->sGetPromotionById('fix', 0, (int) $product['articleID']);
                $promotion['linkDetails'] = $promotion['linkVariant'];
                $products[] = $promotion;
            }
        }

        $properties = $this->sGetComparisonProperties($products);
        $products = $this->sFillUpComparisonArticles($properties, $products);

        return ['articles' => $products, 'properties' => $properties];
    }

    /**
     * Returns all filterable properties depending on the given products
     *
     * @param array $articles
     *
     * @return array
     */
    public function sGetComparisonProperties($articles)
    {
        $shopContext = $this->contextService->getShopContext();
        $properties = [];

        foreach ($articles as $product) {
            //get all properties in the right order
            $sql = "SELECT
                      options.id,
                      options.name,
                      translation.objectdata as translation,
                      translationFallback.objectdata as translationFallback

                    FROM s_filter_options as options
                    LEFT JOIN s_filter_relations as relations ON relations.optionId = options.id
                    LEFT JOIN s_filter as filter ON filter.id = relations.groupID

                    LEFT JOIN s_core_translations AS translation
                    ON translation.objecttype='propertyoption'
                    AND translation.objectkey=options.id
                    AND translation.objectlanguage=:shopId

                    LEFT JOIN s_core_translations AS translationFallback
                    ON translationFallback.objecttype='propertyoption'
                    AND translationFallback.objectkey=options.id
                    AND translationFallback.objectlanguage=:fallbackId

                    WHERE relations.groupID = :groupId
                    AND filter.comparable = 1
                    ORDER BY relations.position ASC";

            $productProperties = $this->db->fetchAll($sql, [
                'groupId' => $product['filtergroupID'],
                'shopId' => $shopContext->getShop()->getId(),
                'fallbackId' => $shopContext->getShop()->getFallbackId(),
            ]);

            foreach ($productProperties as $productProperty) {
                if (!array_key_exists($productProperty['id'], $properties)) {
                    //the key is not part of the array so add it to the end
                    $properties[$productProperty['id']] = $this->extractPropertyTranslation($productProperty);
                }
            }
        }

        return $properties;
    }

    /**
     * fills the product properties with the values and fills up empty values
     *
     * @param array $properties
     * @param array $articles
     *
     * @return array
     */
    public function sFillUpComparisonArticles($properties, $articles)
    {
        foreach ($articles as $productKey => $product) {
            $productProperties = [];
            foreach ($properties as $propertyKey => $property) {
                if (array_key_exists($propertyKey, $product['sProperties'])) {
                    $productProperties[$propertyKey] = $product['sProperties'][$propertyKey];
                } else {
                    $productProperties[$propertyKey] = null;
                }
            }
            $articles[$productKey]['sProperties'] = $productProperties;
        }

        return $articles;
    }

    /**
     * @param array $articleProperty
     *
     * @return string
     */
    private function extractPropertyTranslation($articleProperty)
    {
        if ($articleProperty['translation']) {
            $translation = unserialize($articleProperty['translation'], ['allowed_classes' => false]);
            if ($this->containsTranslation($translation)) {
                return (string) $translation['optionName'];
            }
        }

        if ($articleProperty['translationFallback']) {
            $translation = unserialize($articleProperty['translationFallback'], ['allowed_classes' => false]);
            if ($this->containsTranslation($translation)) {
                return (string) $translation['optionName'];
            }
        }

        return $articleProperty['name'];
    }

    /**
     * @param array $translation
     *
     * @return bool
     */
    private function containsTranslation($translation)
    {
        return is_array($translation) && isset($translation['optionName']) && $translation['optionName'];
    }
}
