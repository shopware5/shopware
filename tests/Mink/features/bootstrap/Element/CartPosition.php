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

use Shopware\Tests\Mink\Helper;

/**
 * Element: CartPosition
 * Location: Cart positions on cart and checkout confirm page
 *
 * Available retrievable properties:
 * - number (string, e.g. "SW10181")
 * - name (string, e.g. "Reisekoffer Set")
 * - quantity (float, e.g. "1")
 * - itemPrice (float, e.g. "139,99")
 * - sum (float, e.g. "139,99")
 */
class CartPosition extends MultipleElement
{
    /**
     * @var array
     */
    protected $selector = ['css' => 'div.row--product'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'name' => 'div.table--content > a.content--title',
            'number' => 'div.table--content > p.content--sku',
            'thumbnailLink' => 'div.table--media a.table--media-link',
            'thumbnailImage' => 'div.table--media a.table--media-link > img',
            'quantity' => 'div.column--quantity option[selected]',
            'itemPrice' => 'div.column--unit-price',
            'sum' => 'div.column--total-price',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedSelectors()
    {
        return [
            'remove' => ['de' => 'Löschen',   'en' => 'Delete'],
        ];
    }

    /**
     * Returns the product name
     *
     * @return string
     */
    public function getNameProperty()
    {
        $elements = Helper::findElements($this, ['name', 'thumbnailLink', 'thumbnailImage']);

        $names = [
            'articleTitle' => $elements['name']->getAttribute('title'),
            'articleThumbnailLinkTitle' => $elements['thumbnailLink']->getAttribute('title'),
            'articleName' => rtrim($elements['name']->getText(), '.'),
        ];

        return $this->getUniqueName($names);
    }

    /**
     * Returns the quantity
     *
     * @return float
     */
    public function getQuantityProperty()
    {
        return $this->getFloatProperty('quantity');
    }

    /**
     * Returns the item price
     *
     * @return float
     */
    public function getItemPriceProperty()
    {
        return $this->getFloatProperty('itemPrice');
    }

    /**
     * Returns the sum
     *
     * @return float
     */
    public function getSumProperty()
    {
        return $this->getFloatProperty('sum');
    }

    /**
     * @param array $names
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getUniqueName(array $names)
    {
        $name = array_unique($names);

        switch (count($name)) {
            //normal case
            case 1:
                return current($name);

            //if articleName is too long, it will be cut. So it's different from the other and has to be checked separately
            case 2:
                $check = [$name];
                $result = Helper::checkArray($check);
                break;

            default:
                $result = false;
                break;
        }

        if ($result !== true) {
            $messages = ['The cart item has different names!'];
            foreach ($name as $key => $value) {
                $messages[] = sprintf('"%s" (Key: "%s")', $value, $key);
            }

            Helper::throwException($messages);
        }

        return $name['articleTitle'];
    }

    /**
     * Helper method to read a float property
     *
     * @param string $propertyName
     *
     * @return float
     */
    protected function getFloatProperty($propertyName)
    {
        $element = Helper::findElements($this, [$propertyName]);

        return Helper::floatValue($element[$propertyName]->getText());
    }
}
