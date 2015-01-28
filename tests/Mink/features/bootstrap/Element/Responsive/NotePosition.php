<?php

namespace Element\Responsive;

class NotePosition extends \Element\Emotion\NotePosition
{
    /** @var array $selector */
    protected $selector = array('css' => 'div.note--item');

    /** @var array $namedSelectors */
    protected $namedSelectors = array(
        'remove'  => array('de' => 'Löschen',       'en' => 'Delete'),
        'compare' => array('de' => 'Vergleichen',   'en' => 'Compare')
    );

    public $cssLocator = array(
        'a-thumb' => 'a.note--image-link',
        'img' => 'img',
        'a-title' => 'a.note--title',
        'p-number' => 'div.note--ordernumber',
        'strong-price' => 'div.note--price',
        'a-detail' => 'a.note--title'
    );

    /**
     * Searches an article from the array, that matches to the NotePosition.
     * If an article was found, the function will return its key, otherwise if no article matches, false will be returned
     * @param  array    $articles
     * @return bool|int
     */
    public function search($articles)
    {
        $elements = \Helper::findElements($this, $this->cssLocator, $this->cssLocator);

        foreach ($articles as $key => $article) {
            $check = array();

            if (!empty($article['name'])) {
                $check[] = array($elements['a-thumb']->getAttribute('title'), $article['name']);
                $check[] = array($elements['img']->getAttribute('alt'), $article['name']);
                $check[] = array($elements['a-title']->getAttribute('title'), $article['name']);
                $check[] = array($elements['a-title']->getText(), $article['name']);
                $check[] = array($elements['a-detail']->getAttribute('title'), $article['name']);
            }

            if (!empty($article['ordernumber'])) {
                $check[] = array($elements['p-number']->getText(), $article['ordernumber']);
            }

            if (!empty($article['price'])) {
                $check[] = \Helper::toFloat(
                    array($elements['strong-price']->getText(), $article['price'])
                );
            }

            if (!empty($article['image'])) {
                $check[] = array($elements['img']->getAttribute('srcset'), $article['image']);
            }

            if (!empty($article['link'])) {
                $check[] = array($elements['a-thumb']->getAttribute('href'), $article['link']);
                $check[] = array($elements['a-title']->getAttribute('href'), $article['link']);
                $check[] = array($elements['a-detail']->getAttribute('href'), $article['link']);
            }

            $result = \Helper::checkArray($check);
            if ($result === true) {
                return $key;
            }
        }

        return false;
    }
}
