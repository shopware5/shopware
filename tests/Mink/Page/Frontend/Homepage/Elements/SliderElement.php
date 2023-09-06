<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Mink\Page\Frontend\Homepage\Elements;

use Behat\Mink\Element\NodeElement;
use Shopware\Tests\Mink\Page\Helper\Elements\MultipleElement;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;

class SliderElement extends MultipleElement
{
    /**
     * If an undefined property method was requested, getSlideProperty() will be called.
     *
     * @param string $name
     * @param array  $arguments
     *
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
     *
     * @param string $property
     *
     * @return string|null
     */
    public function getSlideProperty(NodeElement $slide, $property)
    {
        $selector = Helper::getRequiredSelector($this, 'slide' . $property);

        return $slide->find('css', $selector)->getText();
    }

    /**
     * Returns the slides
     *
     * @param string[] $properties
     *
     * @return array[]
     */
    public function getSlides(array $properties)
    {
        $elements = Helper::findAllOfElements($this, ['slide']);
        $slides = [];

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
