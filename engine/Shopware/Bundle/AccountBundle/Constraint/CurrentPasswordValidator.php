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
     * @var Container
     */
    private $container;

    /**
     * @var \Enlight_Components_Snippet_Manager
     */
    private $snippets;

    /**
     * CurrentPasswordValidator constructor.
     * @param \Enlight_Components_Session_Namespace $session
     * @param Container $container
     * @param \Enlight_Components_Snippet_Manager $snippets
     */
    public function __construct(
        \Enlight_Components_Session_Namespace $session,
        Container $container,
        \Enlight_Components_Snippet_Manager $snippets
    ) {
        $this->session = $session;
        $this->container = $container;
        $this->snippets = $snippets;
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
            $encoderName = $this->container->get('PasswordEncoder')->getDefaultPasswordEncoderName();
        }

        if ($this->container->get('PasswordEncoder')->isPasswordValid($value, $sessionPassword, $encoderName) === false) {
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
