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

namespace Shopware\Tests\Mink\Page\Frontend\Blog\Elements;

use Behat\Mink\Element\NodeElement;
use Shopware\Tests\Mink\Page\Helper\Elements\MultipleElement;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;

/**
 * Element: BlogComment
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class BlogComment extends MultipleElement
{
    /**
     * @var array<string, string>
     */
    protected $selector = ['css' => 'ul.comments--list > li.list--entry'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'author' => '.author--name',
            'date' => '.date--creation',
            'stars' => '.product--rating > .icon--star',
            'half-star' => '.product--rating > .icon--star-half',
            'headline' => '.content--headline',
            'comment' => '.content--comment',
        ];
    }

    /**
     * Returns the star rating
     *
     * @return float
     */
    public function getStarsProperty()
    {
        $elements = Helper::findAllOfElements($this, ['stars', 'half-star'], false);

        return 2 * (\count($elements['stars']) + 0.5 * \count($elements['half-star']));
    }

    /**
     * @return array
     */
    public function getProperties(array $locators)
    {
        $return = [];

        $elements = Helper::findElements($this, $locators);

        foreach ($elements as $locator => $element) {
            $funcName = 'get' . ucfirst($locator);
            $return[$locator] = $this->$funcName($element);
        }

        return $return;
    }

    /**
     * @return string
     */
    protected function getAuthor(NodeElement $element)
    {
        return $element->getText();
    }

    /**
     * @return string
     */
    protected function getDate(NodeElement $element)
    {
        return $element->getText();
    }

    /**
     * @return string
     */
    protected function getStars(NodeElement $element)
    {
        return $element->getAttribute('class');
    }

    /**
     * @return string
     */
    protected function getHeadline(NodeElement $element)
    {
        return $element->getText();
    }

    /**
     * @return string
     */
    protected function getComment(NodeElement $element)
    {
        return $element->getText();
    }
}
