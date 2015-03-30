<?php

namespace Element\Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

class Paging extends Element
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.paging');

    public $cssLocator = array(
        'previous' => 'a.navi.prev',
        'next' => 'a.navi.more'
    );

    /**
     * @param $direction
     * @param int $steps
     */
    public function moveDirection($direction, $steps = 1)
    {
        $locator = array(strtolower($direction));
        $elements = \Helper::findElements($this, $locator);

        for ($i = 0; $i < $steps; $i++) {
            $result = \Helper::countElements($this, $this->cssLocator[$direction], 1);

            if ($result !== true) {
                $result = \Helper::countElements($this, $this->cssLocator[$direction], 2);
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
     * @param $page
     */
    public function moveToPage($page)
    {
        while (!$this->hasLink($page)) {
            if ($this->noElement('next', false)) {
                \Helper::throwException(array('Not found'));

                return;
            }
            $this->moveDirection('next');
        }

        $this->clickLink($page);
    }

    /**
     * @param $element
     * @param  bool $throwException
     * @return bool
     */
    public function noElement($element, $throwException = true)
    {
        if (isset($this->cssLocator[$element])) { //previous or next
            $result = \Helper::countElements($this, $this->cssLocator[$element]);
        } else { //page number (1, 2, 3, 4, ...)
            $result = !$this->hasLink($element);
        }

        if ($result === true) {
            return true;
        }

        if ($throwException) {
            \Helper::throwException(array(sprintf('The Paging Link "%s" exists, but should not!', $element)));
        }

        return false;
    }
}
