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

namespace Shopware\Bundle\FormBundle\Constraints;

use Symfony\Component\Validator\Constraint;

class Exists extends Constraint
{
    /**
     * @var string
     */
    public $message = 'The entry with value ":value" in table ":table.:column" does not exists.';

    /**
     * @var string
     */
    public $table;

    /**
     * @var string
     */
    public $column = 'id';

    /**
     * @var array
     */
    public $conditions = [];

    /**
     * Point to the service tag in DI container
     *
     * @return string
     */
    public function validatedBy()
    {
        return 'shopware.form.constraint.exists';
    }
}
