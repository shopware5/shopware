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

namespace Shopware\Components\Form\Field;

use Shopware\Components\Form\Field;

class Selection extends Field
{
    /**
     * Contains the store data for the selection field.
     *
     * @var array[]
     */
    protected $store;

    /**
     * Requires to set a name for the field
     *
     * @param string  $name
     * @param array[] $store [['text' => 'displayText', 'value'  => 10], ...]
     */
    public function __construct($name, $store)
    {
        $this->name = $name;
        $this->store = $store;
    }

    /**
     * @param array[] $store
     */
    public function setStore($store)
    {
        $this->store = $store;
    }

    /**
     * @return array
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @throws \Exception
     */
    public function validate()
    {
        parent::validate();

        if (!$this->store) {
            throw new \Exception(sprintf(
                'Field %s requires a configured store',
                $this->name
            ));
        }
    }
}
