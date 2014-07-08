<?php

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

/**
 * Class MultipleElement
 */
abstract class MultipleElement extends Element implements Countable, Iterator
{
    /** @var  SubContext */
    private $context;

    /** @var  \Behat\Mink\Selector\CssSelector */
    private $cssSelector;

    /** @var  integer */
    private $count;

    /** @var  integer */
    protected $position;

    /** @var  string */
    private $xPath;

    /**
     * @return string
     */
    public function getXpath()
    {
        return $this->xPath;
    }

    /**
     * @param SubContext $context
     */
    public function setContext(SubContext $context)
    {
        $this->context = $context;

        $session = $context->getSession();
        $selectorsHandler = $session->getSelectorsHandler();
        $this->cssSelector = $selectorsHandler->getSelector('css');

        $this->count = 0;

        foreach ($this as $element) {
            $this->count++;
        }

        $this->position = 1;
    }

    /**
     * @param integer $position
     * @return MultipleElement $this
     */
    public function getInstance($position)
    {
        $locator = sprintf('%s:nth-of-type(%d)', $this->selector['css'], $position);
        $this->xPath = $this->cssSelector->translateToXPath($locator);
        $this->position = $position;

        return $this;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->getInstance($this->position + 1);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        $element = $this->getSession()->getDriver()->find($this->xPath);
        return (!empty($element));
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->getInstance(1);
    }
}