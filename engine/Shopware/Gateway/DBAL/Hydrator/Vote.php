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

        $struct->setId((int)$data['__vote_id']);

        $struct->setName($data['__vote_name']);

        $struct->setPoints((float)$data['__vote_points']);

        $struct->setComment($data['__vote_comment']);

        $struct->setCreatedAt($data['__vote_datum']);

        $struct->setEmail($data['__vote_email']);

        $struct->setHeadline($data['__vote_headline']);

        $struct->setAnswer($data['__vote_answer']);

        $struct->setAnsweredAt($data['__vote_answer_date']);

        return $struct;
    }

}