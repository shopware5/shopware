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

namespace Shopware\Bundle\SearchBundleES;

use ONGR\ElasticsearchDSL\Query\BoolQuery;
use ONGR\ElasticsearchDSL\Query\FuzzyQuery;
use ONGR\ElasticsearchDSL\Query\MatchQuery;
use ONGR\ElasticsearchDSL\Query\MultiMatchQuery;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class SearchTermQueryBuilder implements SearchTermQueryBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildQuery(ShopContextInterface $context, $term)
    {
        return $this->getBestFieldQuery($context, $term);
        
        $boolQuery = new BoolQuery();
        $boolQuery->addParameter('minimum_should_match', 1);
        $boolQuery->add($this->getBestFieldQuery($term), BoolQuery::SHOULD);
//        $boolQuery->add($this->getPhrasePrefixQuery($term), BoolQuery::SHOULD);
        return $boolQuery;
    }

    /**
     * @param ShopContextInterface $context
     * @param string $term
     * @return MultiMatchQuery
     * @throws \Exception
     */
    private function getBestFieldQuery(ShopContextInterface $context, $term)
    {
        $shopAnalyzer = Shopware()->Container()->get('shopware_elastic_search.shop_analyzer');
        $analyzers = $shopAnalyzer->get($context->getShop());

        $query = new BoolQuery();

//        $query->add(
//            new MatchQuery('name', $term, ['boost' => 7, 'fuzziness' => 'AUTO', 'operator' => 'and']),
//            BoolQuery::SHOULD
//        );

        $query->add(
            new MatchQuery('name.german_analyzer', $term, [
                'boost' => 4,
                'fuzziness' => 'AUTO',
                'operator' => 'and'
            ]),
            BoolQuery::SHOULD
        );

//        $query->add(
//            new FuzzyQuery('name.german_analyzer', $term),
//            BoolQuery::SHOULD
//        );

//        foreach ($analyzers as $analyzer) {
//            $query->add(
//                new MatchQuery('name.' . $analyzer, $term, ['boost' => 4, 'fuzziness' => 'AUTO', 'operator' => 'and']),
//                BoolQuery::SHOULD
//            );
//        }

        $query->addParameter('minimum_should_match', '50%');

        return $query;


        return new MultiMatchQuery(
            [
                'name^7',
                'name.*_analyzer^7',

                'keywords^5',
                'keywords.*_analyzer^5',

                'manufacturer.name^3',
                'manufacturer.name.*_analyzer^3',

                'shortDescription',
                'shortDescription.*_analyzer',
            ],
            $term,
            ['type' => 'best_fields', 'minimum_should_match' => '50%', 'tie_breaker' => 0.3]
        );
    }

    /**
     * @param string $term
     * @return MultiMatchQuery
     */
    private function getPhrasePrefixQuery($term)
    {
        return new MultiMatchQuery(
            ['number', 'name'],
            $term,
            ['type' => 'phrase_prefix', 'max_expansions' => 2]
        );
    }
}
