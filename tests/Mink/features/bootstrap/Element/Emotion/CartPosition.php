<?php

namespace Element\Emotion;

use Behat\Mink\Element\NodeElement;
use Element\MultipleElement;

require_once 'tests/Mink/features/bootstrap/Element/MultipleElement.php';

class CartPosition extends MultipleElement
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.table_row:not(.small_quantities):not(.noborder):not(.non):not(.shipping)');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'name' => 'div.basket_details > a.title',
            'number' => 'div.basket_details > p.ordernumber',
            'thumbnailLink' => 'a.thumb_image',
            'thumbnailImage' => 'a.thumb_image > img',
            'quantity' => 'div > form > div:nth-of-type(3) option[selected]',
            'itemPrice' => 'div > form > div:nth-of-type(4)',
            'sum' => 'div > form > div:nth-of-type(5)'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
            'remove'  => array('de' => 'Löschen',   'en' => 'Delete')
        );
    }

    /**
     * Returns the product name
     * @return array
     */
    public function getNameProperty()
    {
        $locators = array('name', 'thumbnailLink', 'thumbnailImage');
        $elements = \Helper::findElements($this, $locators);

        $names = array(
            'articleTitle' => $elements['name']->getAttribute('title'),
            'articleThumbnailLinkTitle' => $elements['thumbnailLink']->getAttribute('title'),
            'articleThumbnailImageAlt' => $elements['thumbnailImage']->getAttribute('alt'),
            'articleName' => rtrim($elements['name']->getText(), '.')
        );

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
                $result = \Helper::checkArray($check);
                break;

            default:
                $result = false;
                break;
        }

        if ($result !== true) {
            $messages = array('The cart item has different names!');
            foreach ($name as $key => $value) {
                $messages[] = sprintf('"%s" (Key: "%s")', $value, $key);
            }

            \Helper::throwException($messages);
        }

        return $name['articleTitle'];
    }
}
