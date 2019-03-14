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

namespace Shopware\Components\Auth\Constraint;

use Doctrine\DBAL\Connection;
use Shopware\Components\Validator\EmailValidatorInterface;
use Shopware_Components_Snippet_Manager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserEmailValidator extends ConstraintValidator
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
        if (!$constraint instanceof UserEmail) {
            return;
        }

        $userId = $constraint->getUserId();

        if (empty($email)) {
            $this->addError($this->getSnippet(self::SNIPPET_MAIL_FAILURE));
        }

        if (!$this->emailValidator->isValid($email)) {
            $this->addError($this->getSnippet(self::SNIPPET_MAIL_FAILURE));
        }

        if ($this->isExistingEmail($email, $userId)) {
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
     * @param string $value
     * @param int    $userId
     *
     * @return bool
     */
    private function isExistingEmail($value, $userId = null)
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select(1);
        $builder->from('s_core_auth');
        $builder->andWhere('email = :email');
        $builder->setParameter('email', $value);

        if ($userId !== null) {
            $builder->andWhere('id != :userId');
            $builder->setParameter('userId', $userId);
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
