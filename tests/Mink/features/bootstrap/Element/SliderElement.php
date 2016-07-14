<?php

namespace Shopware\Tests\Mink\Element;

use Behat\Mink\Element\NodeElement;
use Shopware\Tests\Mink\Helper;

class SliderElement extends MultipleElement
{
    /**
     * If an undefined property method was requested, getSlideProperty() will be called.
     * @param string $name
     * @param array $arguments
     * @return string
     */
    public function __call($name, $arguments)
    {
        preg_match('/^get([A-Z]{1}[a-zA-Z]+)Property$/', $name, $property);

        if (!$property) {
            parent::__call($name, $arguments);
        }

        return $this->getSlideProperty($arguments[0], lcfirst($property[1]));
    }

    /**
     * Default method to get a slide property
     * @param NodeElement $slide
     * @param string $property
     * @return null|string
     */
    public function getSlideProperty(NodeElement $slide, $property)
    {
        $selector = Helper::getRequiredSelector($this, 'slide'.$property);
        return $slide->find('css', $selector)->getText();
    }

    /**
     * Returns the slides
     * @param string[] $properties
     * @return array[]
     */
    public function getSlides(array $properties)
    {
        $elements = Helper::findAllOfElements($this, ['slide']);
        $slides = [];

        /** @var NodeElement $slide */
        foreach ($elements['slide'] as $slide) {
            $slideProperties = [];

            foreach ($properties as $property) {
                $method = 'get' . ucfirst($property) . 'Property';
                $slideProperties[$property] = $this->$method($slide);
            }

            $slides[] = $slideProperties;
        }

        return $slides;
    }
}
