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

use Behat\Mink\Exception\ElementException;
use Shopware\Tests\Mink\Page\Helper\Elements\MultipleElement;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;

/**
 * Element: Banner
 * Location: Emotion element for image banners
 *
 * Available retrievable properties:
 * - image (string, e.g. "deli_teaser503886c2336e3.jpg")
 * - link (string, e.g. "/Campaign/index/emotionId/6")
 * - mapping (array[])
 */
class Banner extends MultipleElement
{
    /**
     * @var array<string, string>
     */
    protected $selector = ['css' => 'div.emotion--banner'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'image' => '.banner--image .banner--image-src',
            'link' => '.banner--link',
            'mapping' => '.banner--mapping-link',
        ];
    }

    /**
     * Returns the banner image
     *
     * @return array
     */
    public function getImageProperty()
    {
        $elements = Helper::findElements($this, ['image']);

        return $elements['image']->getAttribute('src');
    }

    /**
     * Returns the banner link
     *
     * @return string
     */
    public function getLinkProperty()
    {
        $elements = Helper::findElements($this, ['link']);

        return $elements['link']->getAttribute('href');
    }

    /**
     * Returns the banner mapping
     *
     * @return array[]
     */
    public function getMapping()
    {
        $elements = Helper::findAllOfElements($this, ['mapping']);
        $mapping = [];

        foreach ($elements['mapping'] as $link) {
            $mapping[] = ['mapping' => $link->getAttribute('href')];
        }

        return $mapping;
    }

    /**
     * Clicks the banner link
     *
     * @throws ElementException
     */
    public function click()
    {
        $elements = Helper::findElements($this, ['link']);
        $elements['link']->click();
    }
}
