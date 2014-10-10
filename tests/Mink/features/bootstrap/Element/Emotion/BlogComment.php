<?php

namespace Element\Emotion;

use Behat\Mink\Element\NodeElement;
use Element\MultipleElement;

require_once 'tests/Mink/features/bootstrap/Element/MultipleElement.php';

class BlogComment extends MultipleElement
{
    /** @var array $selector */
    protected $selector = array('css' => 'div.comment_outer');

    /** @var array $namedSelectors */
    protected $cssLocator = array(
        'author' => 'div.comment_left > .author',
        'date' => 'div.comment_left > .date',
        'stars' => 'div.comment_left > .star',
        'headline' => 'div.comment_right > .hline',
        'comment' => 'div.comment_right > .comment'
    );

    public function getProperties(array $locators)
    {
        $return = array();

        $elements = \Helper::findElements($this, $locators);

        foreach($elements as $locator => $element)
        {
            $funcName = 'get'.ucfirst($locator);
            $return[$locator] = $this->$funcName($element);
        }

        return $return;
    }

    /**
     * @param NodeElement $element
     * @return string
     */
    protected function getAuthor(NodeElement $element)
    {
        return $element->getText();
    }

    /**
     * @param NodeElement $element
     * @return string
     */
    protected function getDate(NodeElement $element)
    {
        return $element->getText();
    }

    /**
     * @param NodeElement $element
     * @return string
     */
    protected function getStars(NodeElement $element)
    {
        return $element->getAttribute('class');
    }

    /**
     * @param NodeElement $element
     * @return string
     */
    protected function getHeadline(NodeElement $element)
    {
        return $element->getText();
    }

    /**
     * @param NodeElement $element
     * @return string
     */
    protected function getComment(NodeElement $element)
    {
        return $element->getText();
    }

}
