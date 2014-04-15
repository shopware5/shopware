<?php

namespace Shopware\Gateway\DBAL\Hydrator;
use Shopware\Struct as Struct;

class Vote
{
    /**
     * @param array $data
     * @return \Shopware\Struct\VoteAverage
     */
    public function hydrateAverage(array $data)
    {
        $struct = new Struct\VoteAverage();

        $points = 0;
        $total = 0;

        foreach($data as $row) {
            $points += $row['points'];
            $total += $row['total'];
        }

        $struct->setAverage($points / $total);
        $struct->setCount($total);
        $struct->setPointCount($data);

        return $struct;
    }

}