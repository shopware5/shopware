<?php

namespace Element\Emotion;

use Behat\Mink\Element\NodeElement;
use Element\MultipleElement;

require_once 'tests/Mink/features/bootstrap/Element/MultipleElement.php';

class AccountOrder extends MultipleElement
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.orderoverview_active > .table > .table_row');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'date' => 'div > div:nth-of-type(1)',
            'number' => 'div > div:nth-of-type(2)',
            'footerDate' => 'div + .displaynone > .table > .table_foot > div:nth-of-type(2) > p:nth-of-type(1)',
            'footerNumber' => 'div + .displaynone > .table > .table_foot > div:nth-of-type(2) > p:nth-of-type(2)',
            'positions' => 'div + .displaynone > .table > .table_row',
            'product' => '.articleName',
            'currentPrice' => '.currentPrice',
            'quantity' => '.grid_2 > .center',
            'price' => '.grid_3 > .textright',
            'sum' => '.grid_2.bold > .textright'
        );
    }

    /**
     * Returns the order date
     * @return string
     */
    public function getDateProperty()
    {
        $locators = array('date', 'footerDate');
        $elements = \Helper::findElements($this, $locators);

        $dates = array(
            'orderDate' => $elements['date']->getText(),
            'footerDate' => $elements['footerDate']->getText()
        );

        return \Helper::getUnique($dates);
    }

    /**
     * Returns the order number
     * @return string
     */
    public function getNumberProperty()
    {
        $locators = array('number', 'footerNumber');
        $elements = \Helper::findElements($this, $locators);

        $numbers = array(
            'orderNumber' => $elements['number']->getText(),
            'footerNumber' => $elements['footerNumber']->getText()
        );

        return \Helper::getUnique($numbers);
    }

    /**
     * Returns the order positions
     *
     * @param array $locators
     * @return array
     */
    public function getPositions($locators = array('product', 'currentPrice', 'quantity', 'price', 'sum'))
    {
        $selectors = \Helper::getRequiredSelectors($this, $locators);

        $locators = array('positions');
        $elements = \Helper::findAllOfElements($this, $locators);

        $positions = array();

        /** @var NodeElement $position */
        foreach($elements['positions'] as $position)
        {
            $positions[] = $this->getOrderPositionData($position, $selectors);
        }

        return $positions;
    }

    /**
     * Helper function returns the data of an order position
     * @param NodeElement $position
     * @param array $selectors
     * @return array
     */
    private function getOrderPositionData(NodeElement $position, array $selectors)
    {
        $data = array();

        foreach($selectors as $key => $selector) {
            $element = $position->find('css', $selector);

            $data[$key] = $element->getText();

            if ($key !== 'product') {
                $data[$key] = \Helper::floatValue($data[$key]);
            }
        }

        return $data;
    }
}
