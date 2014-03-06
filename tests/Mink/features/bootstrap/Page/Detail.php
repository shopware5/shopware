<?php

use Behat\Mink\Driver\SahiDriver;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Behat\Context\Step;

class Detail extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/detail/index/sArticle/{articleId}';

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     */
    protected function verifyPage()
    {
        if (!$this->hasButton('In den Warenkorb')) {
            throw new \Exception('Detail page has no basket button');
        }
    }

    /**
     * @param int $quantity
     */
    public function toBasket($quantity = 1)
    {
        $this->selectFieldOption('sQuantity', $quantity);
        $this->pressButton('In den Warenkorb');

        if ($this->getSession()->getDriver() instanceof SahiDriver) {
            $this->clickLink('Warenkorb anzeigen');
        }
    }

    public function goToNeighbor($direction)
    {
        $link = $this->find('css', 'a.article_'.$direction);

        if(empty($link))
        {
            $message = sprintf('Detail page has no %s button', $direction);
            throw new ResponseTextException($message, $this->getSession());
        }

        $link->click();
    }
}
