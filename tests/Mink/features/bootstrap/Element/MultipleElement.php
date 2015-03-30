<?php

namespace Element;

require_once 'tests/Mink/features/bootstrap/HelperSelectorInterface.php';

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Behat\Mink\Session;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactoryInterface;

/**
 * Class MultipleElement
 */
abstract class MultipleElement extends Element implements \Countable, \Iterator, \HelperSelectorInterface
{
    /** @var  integer */
    protected $position;

    /** @var  string */
    private $xPath;

    /** @var  array */
    private $siblings;

    public function __construct(Session $session, PageFactoryInterface $pageFactory)
    {
        parent::__construct($session, $pageFactory);

        $this->siblings = array();
    }

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array();
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
     * Have to be called after get the MultipleElement to find all its siblings
     * @param  \Behat\Mink\Element\Element $parent
     * @return $this
     */
    public function setParent(\Behat\Mink\Element\Element $parent)
    {
        $selectorType = key($this->selector);
        $locator = $this->selector[$selectorType];

        $this->siblings = $parent->findAll($selectorType, $locator);

        $this->position = 0;

        if ($this->valid()) {
            $this->setInstance(1);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getXpath()
    {
        return $this->xPath;
    }

    /**
     * @param  integer         $position
     * @return MultipleElement $this
     */
    public function setInstance($position)
    {
        $this->xPath = $this->siblings[$position - 1]->getXpath();

        return $this;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *             </p>
     *             <p>
     *             The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->siblings);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return Element Can return any type.
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
        $this->position++;
        if ($this->valid()) {
            $this->setInstance($this->position + 1);
        }
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
     *                 Returns true on success or false on failure.
     */
    public function valid()
    {
        return isset($this->siblings[$this->position]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->position = 0;
        $this->setInstance(1);
    }
}
