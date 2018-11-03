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
use Shopware\Tests\Mink\Helper;

/**
 * Element: FilterGroup
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class FilterGroup extends MultipleElement
{
    /** @var array $selector */
    protected $selector = ['css' => 'div.filter--container label.filter-panel--title'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'properties' => 'label ~ div.filter-panel--content',
        ];
    }

    /**
     * @param string $propertyName
     *
     * @return bool
     */
    public function setProperty($propertyName)
    {
        $this->expandProperties();

        $elements = Helper::findElements($this, ['properties']);

        /** @var NodeElement $propertyContainer */
        $propertyContainer = $elements['properties'];

        if (!$propertyContainer->hasField($propertyName)) {
            return false;
        }

        $propertyContainer->checkField($propertyName);

        return true;
    }

    /**
     * Helper method to expand the properties of the group
     */
    protected function expandProperties()
    {
        $class = $this->getParent()->getParent()->getAttribute('class');

        if (strpos($class, 'is--collapsed') === false) {
            $this->click();
        }
    }
}
