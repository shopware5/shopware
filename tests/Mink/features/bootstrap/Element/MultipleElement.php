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

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Factory;
use Shopware\Tests\Mink\Helper;

/**
 * Class MultipleElement
 */
abstract class MultipleElement extends Element implements \Countable, \Iterator, \Shopware\Tests\Mink\HelperSelectorInterface
{
    /** @var string */
    private $xPath;

    /** @var NodeElement[] array */
    private $siblings;

    /**
     * Constructor
     *
     * @param Session $session
     * @param Factory $factory
     */
    public function __construct(Session $session, Factory $factory)
    {
        parent::__construct($session, $factory);

        $this->siblings = [];
    }

    /**
     * If an undefined property method was requested, getProperty() will be called.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return string
     */
    public function __call($name, $arguments)
    {
        preg_match('/^get([A-Z]{1}[a-zA-Z]+)Property$/', $name, $property);

        if (!$property) {
            parent::__call($name, $arguments);
        }

        return $this->getProperty(lcfirst($property[1]));
    }

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedSelectors()
    {
        return [];
    }

    /**
     * Have to be called after get the MultipleElement to find all its siblings
     *
     * @param \Behat\Mink\Element\Element $parent
     *
     * @return $this
     */
    public function setParent(\Behat\Mink\Element\Element $parent)
    {
        $selectorType = key($this->selector);
        $locator = $this->selector[$selectorType];

        $this->siblings = $parent->findAll($selectorType, $locator);

        if ($this->valid()) {
            $this->setInstance();
        }

        return $this;
    }

    /**
     * Returns the XPath of the current element
     *
     * @return string
     */
    public function getXpath()
    {
        return $this->xPath;
    }

    /**
     * Sets the instance to the element to use.
     *
     * @param int $position
     *
     * @return MultipleElement $this
     */
    public function setInstance($position = 0)
    {
        $position = ($position > 0) ? $position - 1 : $this->key();
        $this->xPath = $this->siblings[$position]->getXpath();

        return $this;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     *
     * @see http://php.net/manual/en/countable.count.php
     *
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
     *
     * @see http://php.net/manual/en/iterator.current.php
     *
     * @return MultipleElement can return any type
     */
    public function current()
    {
        return $this;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     *
     * @see http://php.net/manual/en/iterator.next.php
     */
    public function next()
    {
        next($this->siblings);
        if ($this->valid()) {
            $this->setInstance();
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     *
     * @see http://php.net/manual/en/iterator.key.php
     *
     * @return mixed scalar on success, or null on failure
     */
    public function key()
    {
        return key($this->siblings);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     *
     * @see http://php.net/manual/en/iterator.valid.php
     *
     * @return bool The return value will be casted to boolean and then evaluated.
     *              Returns true on success or false on failure.
     */
    public function valid()
    {
        return (bool) current($this->siblings);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     *
     * @see http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind()
    {
        reset($this->siblings);
        $this->setInstance();
    }

    /**
     * Removes the current element
     */
    public function remove()
    {
        unset($this->siblings[$this->key()]);
    }

    /**
     * Default method to get an element property
     *
     * @param string $property
     *
     * @return null|string
     */
    public function getProperty($property)
    {
        $element = Helper::findElements($this, [$property]);

        return $element[$property]->getText();
    }
}
