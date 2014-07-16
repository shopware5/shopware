<?php

use SensioLabs\Behat\PageObjectExtension\PageObject\Page, Behat\Mink\Exception\ExpectationException,
    Behat\Mink\Exception\ResponseTextException,
    Behat\Behat\Context\Step;

class Note extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/note';

    /**
     * Removes the article on the given position from the note
     * @param integer $position
     */
    public function removeArticle($position)
    {
        $this->open();
        $this->clickButton($position, 'a.delete');
    }

    /**
     * Put the article on the given position in the cart
     * @param integer $position
     */
    public function buyArticle($position)
    {
        $this->open();
        $this->clickButton($position, 'a.basket');
    }

    /**
     * Add the article on the given position to the comparision list
     * @param integer $position
     */
    public function compareArticle($position)
    {
        $this->open();
        $this->clickButton($position, 'a.compare_add_article');
    }

    /**
     * Visit the detail page of the article on the given position
     * @param integer $position
     */
    public function visitArticleDetails($position)
    {
        $this->open();
        $this->clickButton($position, 'a.detail');
    }

    /**
     * Helper class to click one of the action buttons of the article on the given position
     * @param integer $position
     * @param string $class
     * @throws Behat\Mink\Exception\ResponseTextException
     */
    private function clickButton($position, $class)
    {
        $class = sprintf('div.table_row:nth-of-type(%d) %s', $position + 1, $class);

        $button = $this->find('css', $class);

        if (empty($button)) {
            $message = sprintf('Note page has no article on position %d', $position);
            throw new ResponseTextException($message, $this->getSession());
        }

        $button->click();
    }

    /**
     * Counts the articles on the note
     * If the number is not equal to $count, the helper function will throw an exception $message.
     * If the number is equal to $count, the function will return an array of all articles on the note.
     * @param int $count
     * @return array
     */
    public function countArticles($count = 0)
    {
        $this->open();

        $message = 'There are %d articles on the note (should be %d)';
        $articles = $this->getPage('Helper')->countElements('div.table_row', $message, $count);

        return $articles;
    }

    /**
     * Compares the complete note with the given list of articles
     * @param $articles
     * @throws Behat\Mink\Exception\ResponseTextException
     */
    public function checkList($articles)
    {
        $articlesOnNote = $this->countArticles(count($articles));

        foreach ($articles as $articleKey => $article) {
            foreach ($articlesOnNote as $articleOnNoteKey => $articleOnNote) {

                $locator = sprintf('div.table_row:nth-of-type(%d) ', $articleOnNoteKey + 2);

                $elements = array(
                    'a-thumb' => $this->find('css', $locator . 'a.thumb_image'),
                    'img' => $this->find('css', $locator . 'img'),
                    'a-zoom' => $this->find('css', $locator . 'a.zoom_picture'),
                    'a-title' => $this->find('css', $locator . 'a.title'),
                    'div-supplier' => $this->find('css', $locator . 'div.supplier'),
                    'p-number' => $this->find('css', $locator . 'p.ordernumber'),
                    'p-desc' => $this->find('css', $locator . 'p.desc'),
                    'strong-price' => $this->find('css', $locator . 'strong.price'),
                    'a-detail' => $this->find('css', $locator . 'a.detail'),
                );

                $check = array();

                if (!empty($article['name'])) {
                    $check[] = array($elements['a-thumb']->getAttribute('title'), $article['name']);
                    $check[] = array($elements['img']->getAttribute('alt'), $article['name']);
                    $check[] = array($elements['a-title']->getAttribute('title'), $article['name']);
                    $check[] = array($elements['a-title']->getText(), $article['name']);
                    $check[] = array($elements['a-detail']->getAttribute('title'), $article['name']);
                }

                if (!empty($article['supplier'])) {
                    $check[] = array($elements['div-supplier']->getText(), $article['supplier']);
                }

                if (!empty($article['ordernumber'])) {
                    $check[] = array($elements['p-number']->getText(), $article['ordernumber']);
                }

                if (!empty($article['text'])) {
                    $check[] = array($elements['p-desc']->getText(), $article['text']);
                }

                if (!empty($article['price'])) {
                    $check[] = $this->getPage('Helper')->toFloat(
                        array($elements['strong-price']->getText(), $article['price'])
                    );
                }

                if (!empty($article['image'])) {
                    $check[] = array($elements['img']->getAttribute('src'), $article['image']);
                }

                if (!empty($article['link'])) {
                    $check[] = array($elements['a-thumb']->getAttribute('href'), $article['link']);
                    $check[] = array($elements['a-title']->getAttribute('href'), $article['link']);
                    $check[] = array($elements['a-detail']->getAttribute('href'), $article['link']);
                }

                $result = $this->getPage('Helper')->checkArray($check);
                if ($result === true) {
                    unset($articlesOnNote[$articleOnNoteKey]);
                    break;
                }

                if ($articleOnNote == end($articlesOnNote)) {
                    $message = sprintf(
                        'The article on position %d was not found!',
                        $articleKey + 1
                    );
                    throw new ResponseTextException($message, $this->getSession());
                }
            }
        }
    }
}
