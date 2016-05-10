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

namespace Shopware\Bundle\AccountBundle\Constraint;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Models\Customer\Customer;
use Shopware_Components_Snippet_Manager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EmailValidator;
use Symfony\Component\Validator\ConstraintValidator;

class CustomerEmailValidator extends ConstraintValidator
{
    const SNIPPET_MAIL_FAILURE = [
        'namespace' => 'frontend/account/internalMessages',
        'name' => 'MailFailure',
        'default' => 'Please enter a valid mail address'
    ];

    const SNIPPET_MAIL_DUPLICATE = [
        'namespace' => 'frontend/account/internalMessages',
        'name' => 'MailFailureAlreadyRegistered',
        'default' => 'This mail address is already registered'
    ];

    /**
     * @var Shopware_Components_Snippet_Manager
     */
    private $snippets;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Shopware_Components_Snippet_Manager $snippets
     * @param Connection $connection
     */
    public function __construct(
        Shopware_Components_Snippet_Manager $snippets,
        Connection $connection
    ) {
        $this->snippets = $snippets;
        $this->connection = $connection;
    }

    /**
     * @param string $email
     * @param Constraint $constraint
     */
    public function validate($email, Constraint $constraint)
    {
        if (!$constraint instanceof CustomerEmail) {
            return;
        }

        /** @var CustomerEmail $constraint */
        $shop = $constraint->getShop();

        $customerId = $constraint->getCustomerId();

        if (empty($email)) {
            $this->addError($this->getSnippet(self::SNIPPET_MAIL_FAILURE));
        }

        $this->validateEmailFormat($email);

        if (!$this->isFastLogin($constraint) && $this->isEmailExist($email, $shop, $customerId)) {
            $this->addError($this->getSnippet(self::SNIPPET_MAIL_DUPLICATE));
        }
    }

    /**
     * @param string $message
     */
    private function addError($message)
    {
        $this->context
            ->buildViolation($message)
            ->addViolation();
    }

    /**
     * @param CustomerEmail $constraint
     * @return bool
     */
    private function isFastLogin(CustomerEmail $constraint)
    {
        return $constraint->getAccountMode() == Customer::ACCOUNT_MODE_FAST_LOGIN;
    }

    /**
     * @param string $value
     * @param Shop $shop
     * @param null|int $customerId
     * @return QueryBuilder
     */
    private function isEmailExist($value, Shop $shop, $customerId = null)
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select(1);
        $builder->from('s_user');
        $builder->andWhere('email = :email');
        $builder->andWhere('accountmode != ' . Customer::ACCOUNT_MODE_FAST_LOGIN);
        $builder->setParameter('email', $value);

        if ($shop->hasCustomerScope()) {
            $builder->andWhere('subshopID = :shopId');
            $builder->setParameter('shopId', $shop->getId());
        }

        if ($customerId !== null) {
            $builder->andWhere('id != :userId');
            $builder->setParameter('userId', $customerId);
        }

        $id = $builder->execute()->fetch(\PDO::FETCH_COLUMN);
        return ($id == 1);
    }

    /**
     * @param array $snippet with namespace, name and default value
     * @return string
     */
    private function getSnippet(array $snippet)
    {
        return $this->snippets->getNamespace($snippet['namespace'])->get($snippet['name'], $snippet['default'], true);
    }

    /**
     * @param string $email
     */
    private function validateEmailFormat($email)
    {
        $validator = new EmailValidator();
        $validator->initialize($this->context);

        $emailConstraint = new Email([
            'message' => $this->getSnippet(self::SNIPPET_MAIL_FAILURE)
        ]);

        $validator->validate($email, $emailConstraint);
    }
}
