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

namespace Shopware\Bundle\AccountBundle\Service;

use Shopware\Bundle\AccountBundle\Service\Validator\CustomerValidatorInterface;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Customer\Customer;

class CustomerService implements CustomerServiceInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var CustomerValidatorInterface
     */
    private $validator;

    public function __construct(ModelManager $modelManager, CustomerValidatorInterface $validator)
    {
        $this->modelManager = $modelManager;
        $this->validator = $validator;
    }

    /**
     * @throws ValidationException
     */
    public function update(Customer $customer)
    {
        $this->validator->validate($customer);
        $entities = [$customer];
        if ($customer->getAttribute() instanceof \Shopware\Models\Attribute\Customer) {
            $entities[] = $customer->getAttribute();
        }
        $this->modelManager->flush($entities);
        $this->modelManager->refresh($customer);
    }
}
