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

namespace Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler;

use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundle\Condition\SearchTermCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;

class SearchTermConditionHandler implements ConditionHandlerInterface
{
    public function supports(ConditionInterface $condition)
    {
        return $condition instanceof SearchTermCondition;
    }

    public function handle(ConditionInterface $condition, QueryBuilder $query)
    {
        $fields = [
            'customer.email',
            'customer.title',
            'customer.salutation',
            'customer.firstname',
            'customer.lastname',
            'CAST(customer.birthday AS char)',
            'customer.customernumber',
            'customer.company',
            'customer.department',
            'customer.street',
            'customer.zipcode',
            'customer.city',
            'customer.phone',
            'customer.additional_address_line1',
            'customer.additional_address_line2',
            'CAST(customer.first_order_time AS char)',
            'CAST(customer.last_order_time AS char)',
            'customer.ordered_products',
        ];

        /* @var SearchTermCondition $condition */
        $terms = $this->splitTerm($condition->getTerm());

        foreach ($terms as $index => $term) {
            $where = array_map(function ($field) use ($index) {
                return $field . ' LIKE :searchTerm' . $index;
            }, $fields);

            $query->andWhere(implode(' OR ', $where));

            $query->setParameter(':searchTerm' . $index, '%' . $term . '%');
        }
    }

    /**
     * Parse a string / search term into a keyword array
     *
     * @param string $string
     *
     * @return array
     */
    private function splitTerm($string)
    {
        $string = str_replace(
            ['Ü', 'ü', 'ä', 'Ä', 'ö', 'Ö', 'ß'],
            ['Ue', 'ue', 'ae', 'Ae', 'oe', 'Oe', 'ss'],
            $string
        );

        $string = mb_strtolower(html_entity_decode($string), 'UTF-8');

        // Remove not required chars from string
        $string = trim(preg_replace("/[^\pL_0-9]/u", ' ', $string));

        // Parse string into array
        $wordsTmp = preg_split('/ /', $string, -1, PREG_SPLIT_NO_EMPTY);

        if (count($wordsTmp)) {
            $words = array_unique($wordsTmp);
        } elseif (!empty($string)) {
            $words = [$string];
        } else {
            return [];
        }

        return $words;
    }
}
