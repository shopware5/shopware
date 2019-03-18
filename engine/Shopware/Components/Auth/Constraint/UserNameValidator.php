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
use Shopware\Components\Validator\UserNameValidatorInterface;
use Shopware_Components_Snippet_Manager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserNameValidator extends ConstraintValidator
{
    const SNIPPET_NAME_FAILURE = [
        'namespace' => 'frontend/account/internalMessages',
        'name' => 'NameFailure',
        'default' => 'Please enter a valid user name',
    ];

    const SNIPPET_NAME_DUPLICATE = [
        'namespace' => 'frontend/account/internalMessages',
        'name' => 'NameFailureAlreadyRegistered',
        'default' => 'This user name already exists',
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
     * @var UserNameValidatorInterface
     */
    private $userNameValidator;

    public function __construct(
        Shopware_Components_Snippet_Manager $snippets,
        Connection $connection,
        UserNameValidatorInterface $userNameValidator
    ) {
        $this->snippets = $snippets;
        $this->connection = $connection;
        $this->userNameValidator = $userNameValidator;
    }

    /**
     * @param string $userName
     */
    public function validate($userName, Constraint $constraint)
    {
        if (!$constraint instanceof UserName) {
            return;
        }

        $userId = $constraint->getUserId();

        if (empty($userName)) {
            $this->addError($this->getSnippet(self::SNIPPET_NAME_FAILURE));
        }

        if (!$this->userNameValidator->isValid($userName)) {
            $this->addError($this->getSnippet(self::SNIPPET_NAME_FAILURE));
        }

        if ($this->isExistingUser($userName, $userId)) {
            $this->addError($this->getSnippet(self::SNIPPET_NAME_DUPLICATE));
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
    private function isExistingUser($value, $userId = null)
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select(1);
        $builder->from('s_core_auth');
        $builder->andWhere('username = :username');
        $builder->setParameter('username', $value);

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
