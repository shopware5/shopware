<?php

namespace Shopware\Tests\Mink\Element\Emotion;

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
    protected $selector = array('css' => 'div.comment_block:not(.answer)');

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'author' => 'div.left_container > .author > .name',
            'date' => 'div.left_container > .date',
            'stars' => 'div.left_container > .star',
            'headline' => 'div.right_container > h3',
            'comment' => 'div.right_container > p',
            'answer' => 'div + div.answer > div.right_container'
        ];
    }

    /**
     * Returns the star rating
     * @return float
     */
    public function getStarsProperty()
    {
        $elements = Helper::findElements($this, ['stars']);
        return Helper::floatValue($elements['stars']->getAttribute('class'));
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
