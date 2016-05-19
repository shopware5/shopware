<?php

namespace Shopware\Tests\Mink\Element\Emotion;

use Shopware\Tests\Mink\Element\MultipleElement;
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
     * @var array $selector
     */
    protected $selector = array('css' => 'div.table_row:not(.small_quantities):not(.noborder):not(.non):not(.shipping)');

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'name' => 'div.basket_details > a.title',
            'number' => 'div.basket_details > p.ordernumber',
            'thumbnailLink' => 'a.thumb_image',
            'thumbnailImage' => 'a.thumb_image > img',
            'quantity' => 'div > form > div:nth-of-type(3) option[selected]',
            'itemPrice' => 'div > form > div:nth-of-type(4)',
            'sum' => 'div > form > div:nth-of-type(5)'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'remove'  => ['de' => 'Löschen',   'en' => 'Delete']
        ];
    }

    /**
     * Returns the product name
     * @return string
     */
    public function getNameProperty()
    {
        $elements = Helper::findElements($this, ['name', 'thumbnailLink', 'thumbnailImage']);

        $names = [
            'articleTitle' => $elements['name']->getAttribute('title'),
            'articleThumbnailLinkTitle' => $elements['thumbnailLink']->getAttribute('title'),
            'articleThumbnailImageAlt' => $elements['thumbnailImage']->getAttribute('alt'),
            'articleName' => rtrim($elements['name']->getText(), '.')
        ];

        return $this->getUniqueName($names);
    }

    /**
     * @param array $names
     * @return string
     * @throws \Exception
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
                $check = array($name);
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
     * Returns the quantity
     * @return float
     */
    public function getQuantityProperty()
    {
        return $this->getFloatProperty('quantity');
    }

    /**
     * Returns the item price
     * @return float
     */
    public function getItemPriceProperty()
    {
        return $this->getFloatProperty('itemPrice');
    }

    /**
     * Returns the sum
     * @return float
     */
    public function getSumProperty()
    {
        return $this->getFloatProperty('sum');
    }

    /**
     * Helper method to read a float property
     * @param string $propertyName
     * @return float
     */
    protected function getFloatProperty($propertyName)
    {
        $element = Helper::findElements($this, [$propertyName]);
        return Helper::floatValue($element[$propertyName]->getText());
    }
}
