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
    protected $selector = ['css' => '.sitemap--navigation-head'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'titleLink' => 'a',
            'level1' => 'li ~ ul > li > a',
            'level2' => 'li ~ ul > li > ul > li > a',
        ];
    }

    /**
     * Returns the group title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getText();
    }

    /**
     * Returns the title links
     *
     * @param NodeElement[] $element
     *
     * @return string[]
     */
    public function getTitleLinkData(array $element)
    {
        /** @var NodeElement $titleLink */
        $titleLink = $element[0];

        return [
            'title' => $titleLink->getAttribute('title'),
            'link' => $titleLink->getAttribute('href'),
        ];
    }

    /**
     * Returns the data of entries on 1st level
     *
     * @param NodeElement[] $elements
     *
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
                'link' => $element->getAttribute('href'),
            ];
        }

        return $result;
    }

    /**
     * Returns the data of entries on 2nd level
     *
     * @param NodeElement[] $elements
     *
     * @return array[]
     */
    public function getLevel2Data(array $elements)
    {
        return $this->getLevel1Data($elements);
    }
}
