<?php

namespace Shopware\Tests\Mink;

class TransformContext extends SubContext
{
    /**
     * @Transform /^(\d+)$/
     */
    public function castStringToNumber($string)
    {
        return intval($string);
    }

    /**
     * @Transform /^page "(.*)"$/
     */
    public function castPageNameToPage($pageName)
    {
        return $this->getPage($pageName);
    }
}
