<?php

namespace Shopware\Tests\Mink\Element;

use Behat\Mink\Element\NodeElement;
use Shopware\Tests\Mink\Helper;

/**
 * Element: AccountOrder
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class AccountOrder extends MultipleElement
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => '.order--item'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'date' => '.order--date > .column--value',
            'number' => '.order--number > .column--value',
            'footerDate' => 'div + .order--details .column--info-data > p:nth-of-type(1)',
            'footerNumber' => 'div + .order--details .column--info-data > p:nth-of-type(2)',
            'positions' => 'div + .order--details > .orders--table-header ~ .panel--tr:not(.is--odd)',
            'product' => '.order--name',
            'currentPrice' => '.order--current-price',
            'quantity' => '.order--quantity > .column--value',
            'price' => '.order--price > .column--value',
            'sum' => '.order--amount > .column--value'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getDateProperty()
    {
        $elements = Helper::findElements($this, ['date', 'footerDate']);

        $dates = [
            'orderDate' => $elements['date']->getText(),
            'footerDate' => $elements['footerDate']->getText()
        ];

        return Helper::getUnique($dates);
    }

    /**
     * Returns the order number
     * @return string
     */
    public function getNumberProperty()
    {
        $elements = Helper::findElements($this, ['number', 'footerNumber']);

        $numbers = [
            'orderNumber' => $elements['number']->getText(),
            'footerNumber' => $elements['footerNumber']->getText()
        ];

        return Helper::getUnique($numbers);
    }

    /**
     * Returns the order positions
     * @param string[] $locators
     * @return array[]
     */
    public function getPositions($locators = ['product', 'currentPrice', 'quantity', 'price', 'sum'])
    {
        $selectors = Helper::getRequiredSelectors($this, $locators);
        $elements = Helper::findAllOfElements($this, ['positions']);
        $positions = [];

        /** @var NodeElement $position */
        foreach ($elements['positions'] as $position) {
            $positions[] = $this->getOrderPositionData($position, $selectors);
        }

        return $positions;
    }

    /**
     * Helper function returns the data of an order position
     * @param NodeElement $position
     * @param string[] $selectors
     * @return array
     */
    private function getOrderPositionData(NodeElement $position, array $selectors)
    {
        $data = [];

        foreach ($selectors as $key => $selector) {
            $element = $position->find('css', $selector);

            $data[$key] = $element->getText();

            if ($key !== 'product') {
                $data[$key] = Helper::floatValue($data[$key]);
            }
        }

        return $data;
    }
}
