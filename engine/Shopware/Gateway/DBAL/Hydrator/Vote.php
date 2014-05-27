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

        $struct->setId((int)$data['id']);

        $struct->setName($data['name']);

        $struct->setPoints((float)$data['points']);

        $struct->setComment($data['comment']);

        $struct->setCreatedAt($data['datum']);

        $struct->setEmail($data['email']);

        $struct->setHeadline($data['headline']);

        $struct->setAnswer($data['answer']);

        $struct->setAnsweredAt($data['answer_date']);

        return $struct;
    }

}