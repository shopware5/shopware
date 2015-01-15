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

namespace Shopware\Components\Validator;

use Egulias\EmailValidator\EmailValidator as BaseEmailValidator;

/**
 * The Validator\EmailValidator class is used to validate emails addresses consistently across the system
 *
 * @category  Shopware
 * @package   Shopware\Components\Validator
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class EmailValidator implements EmailValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function isValid($emailAddress)
    {
        /**
         * Creates a new instance of the underlying validator class
         * on each validation request to avoid problems related to
         * state in the validator implementation
         */
        $baseValidator = new BaseEmailValidator();

        return $baseValidator->isValid($emailAddress, false, true);
    }
}
