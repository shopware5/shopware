<?php

namespace Element\Responsive;

use Behat\Mink\Element\NodeElement;

class ArticleEvaluation extends \Element\Emotion\ArticleEvaluation
{
    /** @var array $selector */
    protected $selector = array('css' => 'div.review--entry:not(.is--answer)');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'author' => 'span.content--field:nth-of-type(2)',
            'date' => 'span.content--field:nth-of-type(3)',
            'stars' => 'span.product--rating > meta:nth-of-type(1)',
            'headline' => 'h4.content--title',
            'comment' => 'p.review--content',
            'answer' => 'div + div.is--answer'
        );
    }

    /**
     * @param NodeElement $element
     * @return string
     */
    protected function getStars(NodeElement $element)
    {
        $rating = $element->getAttribute('content');
        $rating = floatval($rating);
        return $rating * 2;
    }

}
