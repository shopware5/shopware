<?php

namespace Element\Emotion;

use Behat\Mink\Element\NodeElement;
use Element\MultipleElement;

require_once 'tests/Mink/features/bootstrap/Element/MultipleElement.php';

class SitemapGroup extends MultipleElement
{
    /** @var array $selector */
    protected $selector = array('css' => '.sitemap > div:not(.clear) > ul > li');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'titleLink' => 'a',
            'level1' => 'li ~ ul > li > a',
            'level2' => 'li ~ ul > li > ul > li > a'
        );
    }

    /**
     * @return null|string
     */
    public function getTitle()
    {
        return $this->getText();
    }

    /**
     * @param array $element
     * @return array
     */
    public function getTitleLinkData(array $element)
    {
        /** @var NodeElement $titleLink */
        $titleLink = $element[0];

        return array(
            'title' => $titleLink->getAttribute('title'),
            'link' => $titleLink->getAttribute('href')
        );
    }

    /**
     * @param array $elements
     * @return array
     */
    public function getLevel1Data(array $elements)
    {
        $result = array();

        /** @var NodeElement $element */
        foreach ($elements as $element) {
            $result[] = array(
                'value' => $element->getText(),
                'title' => $element->getAttribute('title'),
                'link' => $element->getAttribute('href')
            );
        }

        return $result;
    }

    /**
     * @param array $elements
     * @return array
     */
    public function getLevel2Data(array $elements)
    {
        return $this->getLevel1Data($elements);
    }
}
