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
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Components\Validator\EmailValidatorInterface;
use Shopware\Models\Customer\Customer;
use Shopware_Components_Snippet_Manager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CustomerEmailValidator extends ConstraintValidator
{
    const SNIPPET_MAIL_FAILURE = [
        'namespace' => 'frontend/account/internalMessages',
        'name' => 'MailFailure',
        'default' => 'Please enter a valid mail address',
    ];

    const SNIPPET_MAIL_DUPLICATE = [
        'namespace' => 'frontend/account/internalMessages',
        'name' => 'MailFailureAlreadyRegistered',
        'default' => 'This mail address is already registered',
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
     * @var EmailValidatorInterface
     */
    private $emailValidator;

    public function __construct(
        Shopware_Components_Snippet_Manager $snippets,
        Connection $connection,
        EmailValidatorInterface $emailValidator
    ) {
        $this->snippets = $snippets;
        $this->connection = $connection;
        $this->emailValidator = $emailValidator;
    }

    /**
     * @param string $email
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

        if (!$this->emailValidator->isValid($email)) {
            $this->addError($this->getSnippet(self::SNIPPET_MAIL_FAILURE));
        }

        if (!$this->isFastLogin($constraint) && $this->isExistingEmail($email, $shop, $customerId)) {
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
     * @return bool
     */
    private function isFastLogin(CustomerEmail $constraint)
    {
        return $constraint->getAccountMode() == Customer::ACCOUNT_MODE_FAST_LOGIN;
    }

    /**
     * @param string $value
     * @param int    $customerId
     *
     * @return bool
     */
    private function isExistingEmail($value, Shop $shop, $customerId = null)
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select(1);
        $builder->from('s_user');
        $builder->andWhere('email = :email');
        $builder->andWhere('accountmode != ' . Customer::ACCOUNT_MODE_FAST_LOGIN);
        $builder->setParameter('email', $value);

        if ($shop->hasCustomerScope()) {
            $builder->andWhere('subshopID = :shopId');
            $builder->setParameter('shopId', $shop->getParentId());
        }

        if ($customerId !== null) {
            $builder->andWhere('id != :userId');
            $builder->setParameter('userId', $customerId);
        }

        $id = $builder->execute()->fetch(\PDO::FETCH_COLUMN);

        return $id == 1;
    }

    /**
     * @param array $snippet with namespace, name and default value
     *
     * @return string
     */
    private function getSnippet(array $snippet)
    {
        return $this->snippets->getNamespace($snippet['namespace'])->get($snippet['name'], $snippet['default'], true);
    }
}
