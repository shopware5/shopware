<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Mink\Element;

use Behat\Mink\Element\NodeElement;
use Shopware\Tests\Mink\Helper;

/**
 * Element: BannerSlider
 * Location: Emotion element for image banner sliders
 *
 * Available retrievable properties (per slide):
 * - image (string, e.g. "beach1503f8532d4648.jpg")
 * - link (string, e.g. "/Campaign/index/emotionId/6")
 * - alt (string, e.g. "foo")
 * - title (string, e.g. "bar")
 */
class BannerSlider extends SliderElement implements \Shopware\Tests\Mink\HelperSelectorInterface
{
    /**
     * @var array
     */
    protected $selector = ['css' => 'div.emotion--banner-slider'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'slide' => 'div.banner-slider--item',
            'slideImage' => 'img.banner-slider--image',
            'slideLink' => 'div.image-slider--item > a',
        ];
    }

    /**
     * Returns the slide image
     *
     * @return string
     */
    public function getImageProperty(NodeElement $slide)
    {
        $selector = Helper::getRequiredSelector($this, 'slideImage');

        return $slide->find('css', $selector)->getAttribute('src');
    }

    /**
     * Returns the slide link
     *
     * @return string|null
     */
    public function getLinkProperty(NodeElement $slide)
    {
        $selector = Helper::getRequiredSelector($this, 'slideLink');

        return $slide->find('css', $selector)->getAttribute('href');
    }

    /**
     * Returns the alt-attribute of the slide image
     *
     * @return string|null
     */
    protected function getAltProperty(NodeElement $slide)
    {
        $selector = Helper::getRequiredSelector($this, 'slideImage');

        return $slide->find('css', $selector)->getAttribute('alt');
    }

    /**
     * Returns the title-attribute of the slide
     *
     * @return string|null
     */
    protected function getTitleProperty(NodeElement $slide)
    {
        $selector = Helper::getRequiredSelector($this, 'slideImage');

        return $slide->find('css', $selector)->getAttribute('title');
    }
}
