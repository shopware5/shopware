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

namespace Shopware\Bundle\CustomerSearchBundle;

use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;

class BaseCustomer extends Extendable
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $number;

    /**
     * @var string
     */
    protected $email;

    /**
     * @param int         $id
     * @param string      $number
     * @param string      $email
     * @param Attribute[] $attributes
     */
    public function __construct($id, $number, $email, array $attributes = [])
    {
        $this->id = $id;
        $this->number = $number;
        $this->email = $email;
        $this->attributes = $attributes;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
}
