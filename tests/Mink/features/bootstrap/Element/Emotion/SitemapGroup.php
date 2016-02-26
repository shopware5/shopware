<?php

namespace Shopware\Tests\Mink\Element\Emotion;

use Behat\Mink\Element\NodeElement;
use Shopware\Tests\Mink\Element\MultipleElement;

/**
 * Element: SitemapGroup
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class SitemapGroup extends MultipleElement
{
    /** @var array $selector */
    protected $selector = ['css' => '.sitemap > div:not(.clear) > ul > li'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'titleLink' => 'a',
            'level1' => 'li ~ ul > li > a',
            'level2' => 'li ~ ul > li > ul > li > a'
        ];
    }

    /**
     * Returns the group title
     * @return string
     */
    public function getTitle()
    {
        return $this->getText();
    }

    /**
     * Returns the title links
     * @param NodeElement[] $element
     * @return string[]
     */
    public function getTitleLinkData(array $element)
    {
        /** @var NodeElement $titleLink */
        $titleLink = $element[0];

        return [
            'title' => $titleLink->getAttribute('title'),
            'link' => $titleLink->getAttribute('href')
        ];
    }

    /**
     * Returns the data of entries on 1st level
     * @param NodeElement[] $elements
     * @return array[]
     */
    public function getLevel1Data(array $elements)
    {
        $result = [];

        /** @var NodeElement $element */
        foreach ($elements as $element) {
            $result[] = [
                'value' => $element->getText(),
                'title' => $element->getAttribute('title'),
                'link' => $element->getAttribute('href')
            ];
        }

        return $result;
    }

    /**
     * Returns the data of entries on 2nd level
     * @param NodeElement[] $elements
     * @return array[]
     */
    public function getLevel2Data(array $elements)
    {
        return $this->getLevel1Data($elements);
    }
}
