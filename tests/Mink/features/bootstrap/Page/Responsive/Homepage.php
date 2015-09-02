<?php

namespace Shopware\Tests\Mink\Page\Responsive;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\WebAssert;
use Shopware\Tests\Mink\Helper;

class Homepage extends \Shopware\Tests\Mink\Page\Emotion\Homepage
{
    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'newsletterForm' => 'form.newsletter--form',
            'newsletterFormSubmit' => 'form.newsletter--form button[type="submit"]'
        );
    }

    /**
     * @param string $keyword
     */
    public function receiveNoResultsMessageForKeyword($keyword)
    {
        // $keyword gets ignored in responsive template
        $assert = new WebAssert($this->getSession());
        $assert->pageTextContains('Leider wurden zu Ihrer Suchanfrage keine Artikel gefunden');
    }

    /**
     * Changes the currency
     * @param string $currency
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    public function changeCurrency($currency)
    {
        if(!$this->getSession()->getDriver() instanceof Selenium2Driver) {
            Helper::throwException('Changing the currency in Responsive template requires Javascript!');
        }

        $valid = array('EUR' => '€ EUR', 'USD' => '$ USD');
        $this->selectFieldOption('__currency', $valid[$currency]);
    }
}
