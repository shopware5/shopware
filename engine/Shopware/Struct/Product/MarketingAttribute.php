<?php

namespace Shopware\Struct\Product;

use Shopware\Struct\Attribute;

class MarketingAttribute implements Attribute
{
    /**
     * @var bool
     */
    private $isNew = false;

    /**
     * @var bool
     */
    private $isTopSeller = false;

    /**
     * @var bool
     */
    private $comingSoon = false;

    /**
     * @param boolean $comingSoon
     */
    public function setComingSoon($comingSoon)
    {
        $this->comingSoon = $comingSoon;
    }

    /**
     * @param boolean $isNew
     */
    public function setIsNew($isNew)
    {
        $this->isNew = $isNew;
    }

    /**
     * @param boolean $isTopSeller
     */
    public function setIsTopSeller($isTopSeller)
    {
        $this->isTopSeller = $isTopSeller;
    }

    /**
     * @return boolean
     */
    public function comingSoon()
    {
        return $this->comingSoon;
    }

    /**
     * @return boolean
     */
    public function isNew()
    {
        return $this->isNew;
    }

    /**
     * @return boolean
     */
    public function isTopSeller()
    {
        return $this->isTopSeller;
    }


}
