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
use Shopware\Tests\Mink\HelperSelectorInterface;

/**
 * Element: ManufacturerSlider
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class ManufacturerSlider extends SliderElement implements HelperSelectorInterface
{
    /**
     * @var array
     */
    protected $selector = ['css' => 'div.emotion--manufacturer'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'slide' => '.manufacturer--item',
            'slideImage' => '.manufacturer--image',
            'slideLink' => '.manufacturer--link',
        ];
    }

    /**
     * @return string
     */
    public function getImageProperty(NodeElement $slide)
    {
        $selector = Helper::getRequiredSelector($this, 'slideImage');

        return $slide->find('css', $selector)->getAttribute('src');
    }

    /**
     * @return string
     */
    public function getLinkProperty(NodeElement $slide)
    {
        $selector = Helper::getRequiredSelector($this, 'slideLink');

        return $slide->find('css', $selector)->getAttribute('href');
    }

    /**
     * @return string
     */
    public function getNameProperty(NodeElement $slide)
    {
        $selectors = Helper::getRequiredSelectors($this, ['slideImage', 'slideLink']);

        $names = [
            $slide->find('css', $selectors['slideImage'])->getAttribute('alt'),
            $slide->find('css', $selectors['slideLink'])->getAttribute('title'),
        ];

        return Helper::getUnique($names);
    }
}
