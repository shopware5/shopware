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

use Enlight_Components_Session_Namespace as Session;
use Enlight_Components_Snippet_Manager as SnippetManager;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Password\Manager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Constraint validator for CurrentPassword.
 * Checks if the given value (password) matches the password of the logged-in user.
 */
class CurrentPasswordValidator extends ConstraintValidator
{
    private Session $session;

    private SnippetManager $snippets;

    private Manager $passwordManager;

    private ModelManager $modelManager;

    public function __construct(
        Session $session,
        SnippetManager $snippets,
        Manager $passwordManager,
        ModelManager $modelManager
    ) {
        $this->session = $session;
        $this->snippets = $snippets;
        $this->passwordManager = $passwordManager;
        $this->modelManager = $modelManager;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        /** @var CurrentPassword $constraint */
        if (!$constraint instanceof CurrentPassword) {
            return;
        }

        $userData = $this->getUserData();
        if ($userData === null) {
            $this->context
                ->buildViolation('Could not find a customer account corresponding to the current session.')
                ->addViolation();

            return;
        }

        $passwordHash = $userData['password'];
        $encoderName = $userData['encoder'];

        if ($this->passwordManager->isPasswordValid($value, $passwordHash, $encoderName) === false) {
            $errorMessage = $this->snippets
                ->getNamespace($constraint->namespace)
                ->get($constraint->snippetKey);

            $this->context
                ->buildViolation($errorMessage)
                ->atPath($this->context->getPropertyPath())
                ->addViolation();
        }
    }

    /**
     * @return array{password: string, encoder: string}|null
     */
    private function getUserData(): ?array
    {
        $userData = $this->modelManager->getConnection()->createQueryBuilder()
            ->select(['password', 'encoder'])
            ->from('s_user')
            ->where('id = :sUserId')
            ->setParameter('sUserId', $this->session->offsetGet('sUserId'))
            ->execute()
            ->fetch();

        if ($userData === false) {
            return null;
        }

        return $userData;
    }
}
