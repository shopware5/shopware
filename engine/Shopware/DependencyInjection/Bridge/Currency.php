<?php

namespace Shopware\DependencyInjection\Bridge;

class Currency
{
    private $locale;
    private $shop;

    public function __construct(\Zend_Locale $locale, $shop = null)
    {
        $this->locale = $locale;
        $this->shop = $shop;
    }

    /**
     * @return \Zend_Currency
     */
    public function factory()
    {
        $currency = 'EUR';
        if ($this->shop) {
            $currency = $this->shop->getCurrency()->getCurrency();
        }

        return new \Zend_Currency($currency, $this->locale);
    }
}
