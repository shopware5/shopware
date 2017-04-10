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

use Shopware\Components\DependencyInjection\Container;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class sArticlesComparisons
{
    /**
     * @var sArticles
     */
    private $articleModule;

    /**
     * @var sSystem
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
     * @var \Shopware\Bundle\StoreFrontBundle\Context\ContextServiceInterface
     */
    private $contextService;

    /**
     * @param sArticles $articleModule
     * @param Container $container
     */
    public function __construct(sArticles $articleModule, Container $container)
    {
        $this->articleModule = $articleModule;
        $this->systemModule = $articleModule->sSYSTEM;

        $this->db = $container->get('db');
        $this->config = $container->get('config');
        $this->session = $container->get('session');
        $this->contextService = $container->get('storefront.context.service');
    }

    /**
     * Delete articles from comparision chart
     *
     * @param int $articleId Unique article id - refers to s_articles.id
     */
    public function sDeleteComparison($articleId)
    {
        $articleId = (int) $articleId;
        if ($articleId) {
            $this->db->executeUpdate(
                'DELETE FROM s_order_comparisons WHERE sessionID=? AND articleID=?',
                [$this->session->offsetGet('sessionId'), $articleId]
            );
        }
    }

    /**
     * Delete all articles from comparision chart
     */
    public function sDeleteComparisons()
    {
        $sql = '
          DELETE FROM s_order_comparisons WHERE sessionID=?
        ';

        $this->db->executeUpdate($sql, [$this->session->offsetGet('sessionId')]);
    }

    /**
     * Insert articles in comparision chart
     *
     * @param int $articleId s_articles.id
     *
     * @throws Enlight_Exception
     *
     * @return bool true/false
     */
    public function sAddComparison($articleId)
    {
        $articleId = (int) $articleId;
        if (!$articleId) {
            return null;
        }

        // Check if this article is already noted
        $checkForArticle = $this->db->fetchRow(
            'SELECT id FROM s_order_comparisons WHERE sessionID=? AND articleID=?',
            [$this->session->offsetGet('sessionId'), $articleId]
        );

        // Check if max. numbers of articles for one comparison-session is reached
        $checkNumberArticles = $this->db->fetchRow(
            'SELECT COUNT(id) AS countArticles FROM s_order_comparisons WHERE sessionID=?',
            [$this->session->offsetGet('sessionId')]
        );

        if ($checkNumberArticles['countArticles'] >= $this->config->offsetGet('sMAXCOMPARISONS')) {
            return 'max_reached';
        }

        if (!$checkForArticle['id']) {
            $articleName = $this->db->fetchOne(
                'SELECT s_articles.name AS articleName FROM s_articles WHERE id = ?',
                [$articleId]
            );

            if (!$articleName) {
                return false;
            }

            $sql = '
            INSERT INTO s_order_comparisons (sessionID, userID, articlename, articleID, datum)
            VALUES (?,?,?,?,now())
            ';

            $queryNewPrice = $this->db->executeUpdate($sql, [
                $this->session->offsetGet('sessionId'),
                empty($this->session['sUserId']) ? 0 : $this->session['sUserId'],
                $articleName,
                $articleId,
            ]);

            if (!$queryNewPrice) {
                throw new Enlight_Exception('sArticles##sAddComparison##01: Error in SQL-query');
            }
        }

        return true;
    }

    /**
     * Get all articles from comparision chart
     *
     * @return array Associative array with all articles or empty array
     */
    public function sGetComparisons()
    {
        if (!$this->session->offsetGet('sessionId')) {
            return [];
        }

        // Get all comparisons for this user
        $checkForArticle = $this->db->fetchAll(
            'SELECT * FROM s_order_comparisons WHERE sessionID=?',
            [$this->session->offsetGet('sessionId')]
        );

        if (!count($checkForArticle)) {
            return [];
        }

        foreach ($checkForArticle as $k => $article) {
            $checkForArticle[$k]['articlename'] = stripslashes($article['articlename']);
            $checkForArticle[$k] = $this->articleModule->sGetTranslation($article, $article['articleID'], 'article');
            if (!empty($checkForArticle[$k]['articleName'])) {
                $checkForArticle[$k]['articlename'] = $checkForArticle[$k]['articleName'];
            }

            $checkForArticle[$k]['articleId'] = $article['articleID'];
        }

        return $checkForArticle;
    }

    /**
     * Get all articles and a table of their properties as an array
     *
     * @return array Associative array with all articles or empty array
     */
    public function sGetComparisonList()
    {
        if (!$this->session->offsetGet('sessionId')) {
            return [];
        }

        $articles = [];

        // Get all comparisons for this user
        $checkForArticle = $this->db->fetchAll(
            'SELECT * FROM s_order_comparisons WHERE sessionID=?',
            [$this->session->offsetGet('sessionId')]
        );

        if (!count($checkForArticle)) {
            return [];
        }

        foreach ($checkForArticle as $article) {
            if ($article['articleID']) {
                $articles[] = $this->articleModule->sGetPromotionById('fix', 0, (int) $article['articleID']);
            }
        }

        $properties = $this->sGetComparisonProperties($articles);
        $articles = $this->sFillUpComparisonArticles($properties, $articles);

        return ['articles' => $articles, 'properties' => $properties];
    }

    /**
     * Returns all filterable properties depending on the given articles
     *
     * @param array $articles
     *
     * @return array
     */
    public function sGetComparisonProperties($articles)
    {
        $shopContext = $this->contextService->getShopContext();
        $properties = [];

        foreach ($articles as $article) {
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

            $articleProperties = $this->db->fetchAll($sql, [
                'groupId' => $article['filtergroupID'],
                'shopId' => $shopContext->getShop()->getId(),
                'fallbackId' => $shopContext->getShop()->getFallbackId(),
            ]);

            foreach ($articleProperties as $articleProperty) {
                if (!in_array($articleProperty['id'], array_keys($properties))) {
                    //the key is not part of the array so add it to the end
                    $properties[$articleProperty['id']] = $this->extractPropertyTranslation($articleProperty);
                }
            }
        }

        return $properties;
    }

    /**
     * fills the article properties with the values and fills up empty values
     *
     * @param array $properties
     * @param array $articles
     *
     * @return array
     */
    public function sFillUpComparisonArticles($properties, $articles)
    {
        foreach ($articles as $articleKey => $article) {
            $articleProperties = [];
            foreach ($properties as $propertyKey => $property) {
                if (in_array($propertyKey, array_keys($article['sProperties']))) {
                    $articleProperties[$propertyKey] = $article['sProperties'][$propertyKey];
                } else {
                    $articleProperties[$propertyKey] = null;
                }
            }
            $articles[$articleKey]['sProperties'] = $articleProperties;
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
            $translation = unserialize($articleProperty['translation']);
            if ($this->containsTranslation($translation)) {
                return (string) $translation['optionName'];
            }
        }

        if ($articleProperty['translationFallback']) {
            $translation = unserialize($articleProperty['translationFallback']);
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
