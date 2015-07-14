<?php

namespace Element\Responsive;

/**
 * Element: ArticleEvaluation
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class ArticleEvaluation extends \Element\Emotion\ArticleEvaluation
{
    /** @var array $selector */
    protected $selector = ['css' => 'div.review--entry:not(.is--answer)'];

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return [
            'author' => 'span.content--field:nth-of-type(2)',
            'date' => 'span.content--field:nth-of-type(3)',
            'stars' => 'span.product--rating > meta:nth-of-type(1)',
            'headline' => 'h4.content--title',
            'comment' => 'p.review--content',
            'answer' => 'div + div.is--answer'
        ];
    }

    /**
     * @return float
     */
    public function getStarsProperty()
    {
        $elements = \Helper::findElements($this, ['stars']);
        return floatval($elements['stars']->getAttribute('content')) * 2;
    }
}
