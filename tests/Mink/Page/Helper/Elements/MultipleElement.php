<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Mink\Page\Helper\Elements;

use Behat\Mink\Element\Element as MinkElement;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;
use Countable;
use Iterator;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Factory;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;
use Shopware\Tests\Mink\Tests\General\Helpers\HelperSelectorInterface;

abstract class MultipleElement extends Element implements Countable, Iterator, HelperSelectorInterface
{
    private string $xPath = '';

    /**
     * @var array<NodeElement> array
     */
    private array $siblings;

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
     * @return static
     */
    public function setParent(MinkElement $parent)
    {
        \assert(\is_array($this->selector));
        $selectorType = key($this->selector);
        \assert(\is_string($selectorType));
        $locator = $this->selector[$selectorType];

        $this->siblings = $parent->findAll($selectorType, $locator);

        if ($this->valid()) {
            $this->setInstance();
        }

        return $this;
    }

    /**
     * Returns the XPath of the current element
     */
    public function getXpath(): string
    {
        return $this->xPath;
    }

    /**
     * Sets the instance to the element to use.
     *
     * @return static
     */
    public function setInstance(int $position = 0)
    {
        $position = ($position > 0) ? $position - 1 : $this->key();
        $this->xPath = $this->siblings[$position]->getXpath();

        return $this;
    }

    public function count(): int
    {
        return \count($this->siblings);
    }

    /**
     * @return static
     */
    public function current()
    {
        return $this;
    }

    public function next(): void
    {
        next($this->siblings);
        if ($this->valid()) {
            $this->setInstance();
        }
    }

    /**
     * @return mixed scalar on success, or null on failure
     */
    public function key()
    {
        return key($this->siblings);
    }

    public function valid(): bool
    {
        return (bool) current($this->siblings);
    }

    public function rewind(): void
    {
        reset($this->siblings);
        $this->setInstance();
    }

    public function remove(): void
    {
        unset($this->siblings[$this->key()]);
    }

    public function getProperty(string $property): string
    {
        $element = Helper::findElements($this, [$property]);

        return $element[$property]->getText();
    }
}
