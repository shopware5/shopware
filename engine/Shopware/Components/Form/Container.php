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

namespace Shopware\Components\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Form\Interfaces\Container as ContainerInterface;
use Shopware\Components\Form\Interfaces\Element;

class Container extends Base implements ContainerInterface
{
    /**
     * @var ArrayCollection<\Shopware\Components\Form\Interfaces\Element>
     */
    protected $elements;

    /**
     * Contains additional data for each
     * config field.
     *
     * @optional
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Initials the elements collection.
     */
    public function __construct()
    {
        $this->elements = new ArrayCollection();
    }

    /**
     * @param array $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param ArrayCollection<\Shopware\Components\Form\Interfaces\Element> $elements
     */
    public function setElements($elements)
    {
        $this->elements = $elements;
    }

    /**
     * @return ArrayCollection<\Shopware\Components\Form\Interfaces\Element>
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @return $this
     */
    public function addElement(Element $element)
    {
        $this->elements->add($element);

        return $this;
    }
}
