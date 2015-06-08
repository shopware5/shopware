<?php

namespace Element\Emotion;

use Behat\Mink\Element\NodeElement;

require_once 'tests/Mink/features/bootstrap/Element/Emotion/BlogComment.php';

class ArticleEvaluation extends BlogComment
{
    /** @var array $selector */
    protected $selector = array('css' => 'div.comment_block:not(.answer)');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'author' => 'div.left_container > .author > .name',
            'date' => 'div.left_container > .date',
            'stars' => 'div.left_container > .star',
            'headline' => 'div.right_container > h3',
            'comment' => 'div.right_container > p',
            'answer' => 'div + div.answer > div.right_container'
        );
    }

    /**
     * @return float
     */
    public function getStarsProperty()
    {
        $elements = \Helper::findElements($this, ['stars']);
        return \Helper::floatValue($elements['stars']->getAttribute('class'));
    }

    /**
     * @return string
     */
    public function getAnswerProperty()
    {
        $elements = \Helper::findElements($this, ['answer'], false);
        return ($elements['answer']) ? $elements['answer']->getText() : '';
    }
}
