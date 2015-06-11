<?php

namespace Element\Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

class Paging extends Element implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.paging');

    public function getCssSelectors()
    {
        return array(
            'previous' => 'a.navi.prev',
            'next' => 'a.navi.more'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array();
    }

    /**
     * @param $direction
     * @param int $steps
     */
    public function moveDirection($direction, $steps = 1)
    {
        $locator = array(strtolower($direction));
        $elements = \Helper::findElements($this, $locator);

        for ($i = 0; $i < $steps; $i++) {
            $result = \Helper::countElements($this, $direction, 1);

            if ($result !== true) {
                $result = \Helper::countElements($this, $direction, 2);
            }

            if ($result !== true) {
                \Helper::throwException(
                    array(sprintf('There is no more "%s" button! (after %d steps)', $direction, $i))
                );
            }

            $elements[$direction]->click();
        }
    }

    /**
     * @param integer $page
     */
    public function moveToPage($page)
    {
        while (!$this->hasLink($page)) {
            if ($this->noElement('next', false)) {
                $message = sprintf('Page %d was not found!', $page);
                \Helper::throwException($message);
                return;
            }
            $this->moveDirection('next');
        }

        $this->clickLink($page);
    }

    /**
     * @param string $locator
     * @param  bool $throwException
     * @return bool
     */
    public function noElement($locator, $throwException = true)
    {
        if (\Helper::getRequiredSelector($this, $locator)) { //previous or next
            $result = \Helper::countElements($this, $locator);
        } else { //page number (1, 2, 3, 4, ...)
            $result = !$this->hasLink($locator);
        }

        if ($result === true) {
            return true;
        }

        if ($throwException) {
            \Helper::throwException(array(sprintf('The Paging Link "%s" exists, but should not!', $locator)));
        }

        return false;
    }
}
