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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator;

use Shopware\Bundle\StoreFrontBundle\Struct;

class VoteHydrator extends Hydrator
{
    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Product\VoteAverage
     */
    public function hydrateAverage(array $data)
    {
        $struct = new Struct\Product\VoteAverage();

        $points = 0;
        $total = 0;

        foreach ($data as $row) {
            $points += $row['points'] * $row['total'];
            $total += $row['total'];
        }

        $this->sortByPointsDescending($data);

        $struct->setAverage($points / $total * 2);
        $struct->setCount($total);
        $struct->setPointCount($data);

        return $struct;
    }

    /**
     * @return Struct\Product\Vote
     */
    public function hydrate(array $data)
    {
        $struct = new Struct\Product\Vote();

        if (isset($data['__vote_id'])) {
            $struct->setId((int) $data['__vote_id']);
        }

        if (isset($data['__vote_name'])) {
            $struct->setName($data['__vote_name']);
        }

        if (isset($data['__vote_points'])) {
            $struct->setPoints((float) $data['__vote_points']);
        }

        if (isset($data['__vote_comment'])) {
            $struct->setComment($data['__vote_comment']);
        }

        if (isset($data['__vote_datum']) && $data['__vote_datum'] != '0000-00-00 00:00:00') {
            $struct->setCreatedAt(
                new \DateTime($data['__vote_datum'])
            );
        }

        if (isset($data['__vote_email'])) {
            $struct->setEmail($data['__vote_email']);
        }

        if (isset($data['__vote_headline'])) {
            $struct->setHeadline($data['__vote_headline']);
        }

        if (isset($data['__vote_answer'])) {
            $struct->setAnswer($data['__vote_answer']);
        }

        if (isset($data['__vote_answer_date'])) {
            $struct->setAnsweredAt(
                new \DateTime($data['__vote_answer_date'])
            );
        }

        return $struct;
    }

    /**
     * @param array $data
     */
    private function sortByPointsDescending($data)
    {
        usort($data, function ($a, $b) {
            if ($a['points'] == $b['points']) {
                return 0;
            }

            return ($a['points'] > $b['points']) ? -1 : 1;
        });
    }
}
