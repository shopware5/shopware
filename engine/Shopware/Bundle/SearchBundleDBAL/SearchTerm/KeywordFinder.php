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

namespace Shopware\Bundle\SearchBundleDBAL\SearchTerm;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundleDBAL\KeywordFinderInterface;

class KeywordFinder implements KeywordFinderInterface
{
    /**
     * Max keywords that should be considered in search
     *
     * @var int
     */
    protected $maxKeywords = 5;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * @var TermHelperInterface
     */
    private $termHelper;

    public function __construct(
        \Shopware_Components_Config $config,
        Connection $connection,
        TermHelperInterface $termHelper
    ) {
        $this->config = $config;
        $this->connection = $connection;
        $this->termHelper = $termHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getKeywordsOfTerm($term)
    {
        // Set terms to search on, limit to maxKeywords
        $keywords = array_slice($this->termHelper->splitTerm($term), 0, $this->maxKeywords);

        // If any term in search request
        if (empty($keywords)) {
            return [];
        }

        $matches = [];
        foreach ($keywords as $searchTerm) {
            $matches = array_merge($matches, $this->searchMatchingKeywords($searchTerm));
        }

        return $matches;
    }

    /**
     * For a certain term get matching keywords from keyword index
     *
     * @param string $term
     *
     * @return Keyword[]
     */
    private function searchMatchingKeywords($term)
    {
        return array_merge(
            $this->findDirectMatches($term),
            $this->findFuzzyMatches($term)
        );
    }

    /**
     * @param string $term
     *
     * @return Keyword[]
     */
    private function findDirectMatches($term)
    {
        $sql = '
            SELECT `id` , `keyword`
            FROM `s_search_keywords`
            WHERE keyword = ?
        ';

        $directMatches = $this->connection->fetchAll($sql, [$term]);

        $results = [];
        foreach ($directMatches as $keyword) {
            $results[] = new Keyword(
                $keyword['id'],
                $this->config->get('fuzzySearchExactMatchFactor', 100),
                $term,
                $keyword
            );
        }

        return $results;
    }

    /**
     * @param string $term
     *
     * @return Keyword[]
     */
    private function findFuzzyMatches($term)
    {
        $sql = '
            SELECT `id` , `keyword`
            FROM `s_search_keywords`
            WHERE keyword LIKE CONCAT(\'%\',?,\'%\')
            OR keyword LIKE CONCAT(LEFT(?,2),\'%\')
        ';

        $fuzzyMatches = $this->connection->fetchAll($sql, [$term, $term]);

        $results = [];
        foreach ($fuzzyMatches as $keyword) {
            $keywordID = $keyword['id'];
            $keyword = $keyword['keyword'];

            if (strlen($term) < strlen($keyword)) {
                $term1 = $keyword;
                $term2 = $term;
            } else {
                $term2 = $keyword;
                $term1 = $term;
            }

            $relevance = 0;

            // Check for sub term matching
            if (strpos($term1, $term2) !== false) {
                if (strlen($term1) < 4) {
                    $relevance = $this->config->get('fuzzySearchMatchFactor', 5);

                //ipod === ipods
                } elseif (strlen($term1) - strlen($term2) <= 1) {
                    $relevance = $this->config->get('fuzzySearchExactMatchFactor', 100);

                //digital == digi
                } elseif ((round(strlen($term2) / strlen($term1), 2) * 100) >= $this->config->get('fuzzySearchPartNameDistancen', 25)) {
                    $relevance = $this->config->get('fuzzySearchPatternMatchFactor', 50);
                }
            }

            if (!empty($relevance)) {
                $results[] = new Keyword(
                    $keywordID,
                    $relevance,
                    $term,
                    $keyword
                );
            }
        }

        return $results;
    }
}
