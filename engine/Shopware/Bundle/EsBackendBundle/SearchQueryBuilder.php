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

namespace Shopware\Bundle\EsBackendBundle;

use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\FullText\MatchQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\WildcardQuery;

class SearchQueryBuilder
{
    /**
     * @param string $term
     *
     * @return BoolQuery
     */
    public function buildQuery(array $fields, $term)
    {
        $tokens = $this->tokenize($term);

        $combines = $this->combine($tokens);

        $bool = new BoolQuery();
        foreach ($tokens as $token) {
            foreach ($fields as $field => $priority) {
                $bool->add(new MatchQuery($field, $token, ['boost' => $priority]), BoolQuery::SHOULD);
                $bool->add(new WildcardQuery($field, '*' . strtolower($token) . '*'), BoolQuery::SHOULD);
            }
        }

        //use combination for more precision
        foreach ($combines as $token) {
            foreach ($fields as $field => $priority) {
                $bool->add(new MatchQuery($field, $token, ['boost' => $priority * 2]), BoolQuery::SHOULD);
            }
        }

        return $bool;
    }

    /**
     * @param string $term
     *
     * @return string[]
     */
    private function tokenize($term)
    {
        $string = mb_strtolower(html_entity_decode($term), 'UTF-8');
        $string = trim(str_replace(['.', '-', '/', '\\'], ' ', $string));
        $string = str_replace('<', ' <', $string);
        $string = strip_tags($string);
        $string = trim(preg_replace("/[^\pL_0-9]/u", ' ', $string));

        $tokens = array_unique(explode(' ', $string));
        $tokens = array_map('trim', $tokens);

        $tokens = array_filter(
            array_filter(
                $tokens,
                function ($token) {
                    return strlen($token) >= 2;
                }
            )
        );

        return $tokens;
    }

    /**
     * @param string[] $items
     *
     * @return string[]
     */
    private function combine($items)
    {
        $result = [];

        for ($i = 1; $i < 3; ++$i) {
            $combination = [];
            for ($x = 0; $x <= $i; ++$x) {
                $combination[] = $items[$x];
            }

            $result[] = implode(' ', $combination);
        }

        return $result;
    }
}
