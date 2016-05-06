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

use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Password\Manager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Constraint validator for CurrentPassword.
 * Checks if the given value (password) matches the password of the logged-in user.
 *
 * @package Shopware\Bundle\AccountBundle\Constraint
 */
class CurrentPasswordValidator extends ConstraintValidator
{
    /**
     * @var \Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * @var \Enlight_Components_Snippet_Manager
     */
    private $snippets;

    /**
     * @var Manager
     */
    private $passwordManager;

    /**
     * CurrentPasswordValidator constructor.
     * @param \Enlight_Components_Session_Namespace $session
     * @param \Enlight_Components_Snippet_Manager $snippets
     * @param Manager $passwordManager
     */
    public function __construct(
        \Enlight_Components_Session_Namespace $session,
        \Enlight_Components_Snippet_Manager $snippets,
        Manager $passwordManager
    ) {
        $this->session = $session;
        $this->snippets = $snippets;
        $this->passwordManager = $passwordManager;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        /** @var CurrentPassword  $constraint */
        if ($constraint instanceof CurrentPassword === false) {
            return;
        }

        $extraData = $this->context->getRoot()->getExtraData();
        $sessionPassword = $this->session->offsetGet('sUserPassword');
        $encoderName = $extraData['encoderName'];

        if (empty($encoderName)) {
            $encoderName = $this->passwordManager->getDefaultPasswordEncoderName();
        }

        if ($this->passwordManager->isPasswordValid($value, $sessionPassword, $encoderName) === false) {
            $errorMessage = $this->snippets
                ->getNamespace($constraint->namespace)
                ->get($constraint->snippetKey);

            $this->context
                ->buildViolation($errorMessage)
                ->atPath($this->context->getPropertyPath())
                ->addViolation();
        }
    }
}
