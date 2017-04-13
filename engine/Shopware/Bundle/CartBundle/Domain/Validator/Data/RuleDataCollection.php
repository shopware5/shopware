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

namespace Shopware\Bundle\CartBundle\Domain\Validator\Data;

use Shopware\Bundle\StoreFrontBundle\Common\Collection;

class RuleDataCollection extends Collection
{
    /**
     * @var RuleData[]
     */
    protected $elements = [];

    public function add(RuleData $ruleData): void
    {
        $this->elements[$this->getKey($ruleData)] = $ruleData;
    }

    public function remove(string $class): void
    {
        parent::doRemoveByKey($class);
    }

    public function removeElement(RuleData $ruleData): void
    {
        parent::doRemoveByKey($this->getKey($ruleData));
    }

    public function exists(RuleData $ruleData): bool
    {
        return parent::has($this->getKey($ruleData));
    }

    public function get(string $class): ? RuleData
    {
        if ($this->has($class)) {
            return $this->elements[$class];
        }

        return null;
    }

    protected function getKey(RuleData $element)
    {
        return get_class($element);
    }
}
