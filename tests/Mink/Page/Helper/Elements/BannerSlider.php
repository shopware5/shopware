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

namespace Shopware\Tests\Mink\Page\Helper\Elements;

use Behat\Mink\Element\NodeElement;
use Shopware\Tests\Mink\Page\Frontend\Homepage\Elements\SliderElement;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;

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
class BannerSlider extends SliderElement
{
    /**
     * @var array<string, string>
     */
    protected $selector = ['css' => 'div.emotion--banner-slider'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors(): array
    {
        return [
            'slide' => 'div.banner-slider--item',
            'slideImage' => 'img.banner-slider--image',
            'slideLink' => 'div.image-slider--item > a',
        ];
    }

    /**
     * Returns the slide image
     */
    public function getImageProperty(NodeElement $slide): string
    {
        $selector = Helper::getRequiredSelector($this, 'slideImage');

        return $slide->find('css', $selector)->getAttribute('src');
    }

    /**
     * Returns the slide link
     */
    public function getLinkProperty(NodeElement $slide): ?string
    {
        $selector = Helper::getRequiredSelector($this, 'slideLink');

        return $slide->find('css', $selector)->getAttribute('href');
    }

    /**
     * Returns the alt-attribute of the slide image
     */
    protected function getAltProperty(NodeElement $slide): ?string
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
