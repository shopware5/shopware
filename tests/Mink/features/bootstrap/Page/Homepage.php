<?php

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Behat\Context\Step;

class Homepage extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/';

    /**
     * @param string $searchTerm
     * @return array
     */
    public function searchFor($searchTerm)
    {
        $this->fillField('searchfield', $searchTerm);
        $this->pressButton('submit_search_btn');
        $this->verifyResponse();
    }
}
