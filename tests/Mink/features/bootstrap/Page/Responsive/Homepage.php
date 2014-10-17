<?php

namespace Page\Responsive;

use Behat\Behat\Context\Step;
use Behat\Mink\Driver\SahiDriver;
use Symfony\Component\Console\Helper\Helper;

class Homepage extends \Page\Emotion\Homepage
{
    public $cssLocator = array(
        'contentBlock' => 'section.content-main > div.content-main--inner',
        'searchForm' => 'form.main-search--form',
        'newsletterForm' => 'form.newsletter--form',
        'newsletterFormSubmit' => 'form.newsletter--form button[type="submit"]',
        'controller' => array(
            'account' => 'body.is--ctl-account',
            'checkout' => 'body.is--ctl-checkout',
            'newsletter' => 'body.is--ctl-newsletter'
        )
    );

    /**
     * @param string $keyword
     */
    public function receiveNoResultsMessageForKeyword($keyword)
    {
        // $keyword gets ignored in responsive template
        $assert = new \Behat\Mink\WebAssert($this->getSession());
        $assert->pageTextContains('Leider wurden zu Ihrer Suchanfrage keine Artikel gefunden');
    }

    /**
     * Changes the currency
     * @param string $currency
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    public function changeCurrency($currency)
    {
        if(!$this->getSession()->getDriver() instanceof SahiDriver) {
            \Helper::throwException('Changing the currency in Responsive template requires Javascript!');
        }

        $valid = array('EUR' => '€ EUR', 'USD' => '$ USD');
        $this->selectFieldOption('__currency', $valid[$currency]);
    }
}
