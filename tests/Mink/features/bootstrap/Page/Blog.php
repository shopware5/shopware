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
     * @param $count
     * @throws Behat\Mink\Exception\ResponseTextException
     */
    public function countArticles($count)
    {
        $articles = $this->findAll('css', 'div.blogbox');

        if (count($articles) != $count) {
            $message = sprintf('There are %d blog articles (should be %d)', count($articles), $count);
            throw new ResponseTextException($message, $this->getSession());
        }
    }

}