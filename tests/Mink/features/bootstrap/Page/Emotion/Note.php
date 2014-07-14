<?php
namespace Emotion;

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
     * Counts the articles on the note
     * If the number is not equal to $count, the helper function will throw an exception $message.
     * If the number is equal to $count, the function will return an array of all articles on the note.
     * @param int $count
     * @return array
     */
    public function countArticles($count = 0)
    {
        $this->open();

        $result = \Helper::countElements($this, 'div.table_row', $count);

        if($result !== true)
        {
            $message = sprintf('There are %d articles on the note (should be %d)', $result, $count);
            \Helper::throwException(array($message));
        }
    }

    /**
     * Compares the complete note with the given list of articles
     * @param $articles
     * @throws \Behat\Mink\Exception\ResponseTextException
     */
    public function checkList($articles)
    {
        $this->countArticles(count($articles));

        $articlesOnNote = $this->findAll('css', 'div.table_row');

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
                    $check[] = \Helper::toFloat(
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

                $result = \Helper::checkArray($check);
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
