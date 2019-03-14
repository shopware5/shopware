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

namespace Shopware\Components\Model;

use Shopware\Bundle\SearchBundleDBAL\SearchTerm\TermHelperInterface;

class SearchBuilder
{
    /**
     * @var TermHelperInterface
     */
    private $termHelper;

    public function __construct(TermHelperInterface $termHelper)
    {
        $this->termHelper = $termHelper;
    }

    /**
     * @param QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder $query
     * @param string                                         $term
     * @param array                                          $fields e.g. ['article.name^2', 'article.description^1'] "^1" for boosting
     */
    public function addSearchTerm($query, $term, array $fields)
    {
        $terms = $this->termHelper->splitTerm($term);

        if (empty($terms)) {
            return;
        }

        $conditions = [];
        $select = [];

        foreach ($fields as $field) {
            $parts = explode('^', $field);
            $field = $parts[0];
            $ranking = isset($parts[1]) ? (float) $parts[1] : 1;

            foreach ($terms as $index => $word) {
                $conditions[] = $field . '  LIKE :phrase' . $index;
                $select[] = sprintf(' IF(%s LIKE %s, %s, 0) ', $field, ':phrase' . $index, $ranking * 0.5);
                $select[] = sprintf(' IF(%s = %s, %s, 0) ', $field, ':match' . $index, $ranking);
            }

            $select[] = sprintf(' IF(%s = :full, %s, 0) ', $field, $ranking * 2);
        }

        foreach ($terms as $index => $word) {
            $query->setParameter(':phrase' . $index, '%' . $word . '%');
            $query->setParameter(':match' . $index, $word);
        }
        $query->setParameter(':full', $term);

        $select = implode(' + ', $select);
        $query->addSelect('(' . $select . ') as _score');
        $query->orderBy('_score', 'DESC');
        $query->andWhere(implode(' OR ', $conditions));
    }
}
