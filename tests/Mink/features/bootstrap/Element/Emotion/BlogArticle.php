<?php

namespace Element\Emotion;

use Element\MultipleElement;

require_once 'tests/Mink/features/bootstrap/Element/MultipleElement.php';

class BlogArticle extends MultipleElement implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.emotion-element > div.blog-element');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'title' => 'h2 > a',
            'link' => 'div.blog_img > a',
            'text' => 'p'
        );
    }

    /**
     * @return array
     */
    public function getTitlesToCheck()
    {
        $locators = array('title', 'link');
        $elements = \Helper::findAllOfElements($this, $locators);

        $titles = array();

        foreach ($elements['title'] as $key => $title) {
            $titles[] = array(
                $title->getText(),
                $title->getAttribute('title'),
                $elements['link'][$key]->getAttribute('title')
            );
        }

        return $titles;
    }

    /**
     * @return array
     */
    public function getImagesToCheck()
    {
        $locators = array('link');
        $elements = \Helper::findAllOfElements($this, $locators);

        $images = array();

        foreach ($elements['link'] as $image) {
            $images[] = array($image->getAttribute('style'));
        }

        return $images;
    }

    /**
     * @return array
     */
    public function getLinksToCheck()
    {
        $locators = array('title', 'link');
        $elements = \Helper::findAllOfElements($this, $locators);

        $links = array();

        foreach ($elements['title'] as $key => $title) {
            $links[] = array(
                $title->getAttribute('href'),
                $elements['link'][$key]->getAttribute('href')
            );
        }

        return $links;
    }

    /**
     * @return array
     */
    public function getTextsToCheck()
    {
        $locators = array('text');
        $elements = \Helper::findAllOfElements($this, $locators);

        $texts = array();

        foreach ($elements['text'] as $text) {
            $texts[] = array($text->getText());
        }

        return $texts;
    }
}