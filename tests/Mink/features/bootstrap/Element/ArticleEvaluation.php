<?php

namespace Shopware\Tests\Mink\Element;

use Shopware\Tests\Mink\Helper;

/**
 * Element: ArticleEvaluation
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class ArticleEvaluation extends BlogComment
{
    /** @var array $selector */
    protected $selector = ['css' => 'div.review--entry:not(.is--answer)'];

    /**
     * @inheritdoc
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
     * Returns the star rating
     * @return float
     */
    public function getStarsProperty()
    {
        $elements = Helper::findElements($this, ['stars']);
        return floatval($elements['stars']->getAttribute('content')) * 2;
    }

    /**
     * Returns the shop owners answer to customers evaluation
     * @return string
     */
    public function getAnswerProperty()
    {
        $elements = Helper::findElements($this, ['answer'], false);
        return ($elements['answer']) ? $elements['answer']->getText() : '';
    }
}
