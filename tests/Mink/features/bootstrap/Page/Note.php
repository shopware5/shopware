<?php

use SensioLabs\Behat\PageObjectExtension\PageObject\Page,
        Behat\Mink\Exception\ResponseTextException,
        Behat\Behat\Context\Step;


class Note extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/note';

    public function removeArticle($position)
    {
        $this->open();
        $this->clickButton($position, 'a.delete');
    }

    public function buyArticle($position)
    {
        $this->open();
        $this->clickButton($position, 'a.basket');
    }

    public function compareArticle($position)
    {
        $this->open();
        $this->clickButton($position, 'a.compare_add_article');
    }

    public function visitArticleDetails($position)
    {
        $this->open();
        $this->clickButton($position, 'a.detail');
    }

    private function clickButton($position, $class)
    {
        $class = sprintf('div.table_row:nth-of-type(%d) %s', $position + 1, $class);

        $button = $this->find('css', $class);

        if(empty($button))
        {
            $message = sprintf('Note page has no article on position %d', $position);
            throw new ResponseTextException($message, $this->getSession());
        }

        $button->click();
    }
}
