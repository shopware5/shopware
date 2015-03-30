<?php

namespace Element\Emotion;

use Element\MultipleElement;

require_once 'tests/Mink/features/bootstrap/Element/MultipleElement.php';

class Banner extends MultipleElement implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.emotion-element > div.banner-element');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'image' => 'img',
            'link' => 'div.mapping > a',
            'mapping' => 'div.banner-mapping > a.emotion-banner-mapping'
        );
    }

    /**
     * @return array
     */
    public function getImagesToCheck()
    {
        $locators = array('image');
        $elements = \Helper::findElements($this, $locators);

        return array(
            'image' => $elements['image']->getAttribute('src')
        );
    }

    /**
     * @return array
     */
    public function getLinksToCheck()
    {
        $locators = array('link');
        $elements = \Helper::findElements($this, $locators);

        return array(
            'link' => $elements['link']->getAttribute('href')
        );
    }

    /**
     * @return array
     */
    public function getMappingsToCheck()
    {
        $locators = array('mapping');
        $elements = \Helper::findAllOfElements($this, $locators);

        $mapping = array();

        foreach ($elements['mapping'] as $link) {
            $mapping[] = array($link->getAttribute('href'));
        }

        return $mapping;
    }

    public function click()
    {
        $locators = array('link');
        $elements = \Helper::findElements($this, $locators);

        $elements['link']->click();
    }
}