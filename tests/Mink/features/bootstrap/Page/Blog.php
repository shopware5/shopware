<?php

use Behat\Mink\Driver\SahiDriver;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Behat\Context\Step;

class Blog extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/blog/index/sCategory/{categoryId}';

    /**
     * Counts the blog articles
     * If the number is not equal to $count, the helper function will throw an exception $message.
     * @param int $count
     */
    public function countArticles($count = 0)
    {
        $message = 'There are %d blog articles (should be %d)';
        $this->getPage('Helper')->countElements('div.blogbox', $message, $count);
    }

}