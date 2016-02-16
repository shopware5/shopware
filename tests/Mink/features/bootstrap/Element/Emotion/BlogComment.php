<?php

namespace Shopware\Tests\Mink\Element\Emotion;

use Behat\Mink\Element\NodeElement;
use Shopware\Tests\Mink\Element\MultipleElement;
use Shopware\Tests\Mink\Helper;

/**
 * Element: BlogComment
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class BlogComment extends MultipleElement
{
    /** @var array $selector */
    protected $selector = array('css' => 'div.comment_outer');

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'author' => 'div.comment_left > .author',
            'date' => 'div.comment_left > .date',
            'stars' => 'div.comment_left > .star',
            'headline' => 'div.comment_right > .hline',
            'comment' => 'div.comment_right > .comment'
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
     * @param array $locators
     * @return array
     */
    public function getProperties(array $locators)
    {
        $return = array();

        $elements = Helper::findElements($this, $locators);

        foreach ($elements as $locator => $element) {
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
