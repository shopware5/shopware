<?php

namespace Shopware\Gateway\DBAL\Hydrator;

use Shopware\Struct as Struct;

class Vote extends Hydrator
{
    /**
     * @param array $data
     * @return \Shopware\Struct\Product\VoteAverage
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

        $struct->setAverage($points / $total);
        $struct->setCount($total);
        $struct->setPointCount($data);

        return $struct;
    }

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

}
