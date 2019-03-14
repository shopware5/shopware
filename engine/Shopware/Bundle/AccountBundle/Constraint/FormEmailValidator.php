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

use Shopware\Models\Customer\Customer;
use Shopware_Components_Snippet_Manager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class FormEmailValidator extends ConstraintValidator
{
    const SNIPPET_EMAIL_CONFIRMATION = [
        'namespace' => 'frontend/account/internalMessages',
        'name' => 'MailFailureNotEqual',
        'default' => 'The mail addresses entered are not equal',
    ];

    /**
     * @var Shopware_Components_Snippet_Manager
     */
    private $snippets;

    /**
     * @var CustomerEmailValidator
     */
    private $customerEmailValidator;

    public function __construct(
        Shopware_Components_Snippet_Manager $snippets,
        CustomerEmailValidator $customerEmailValidator
    ) {
        $this->snippets = $snippets;
        $this->customerEmailValidator = $customerEmailValidator;
    }

    /**
     * @param string $email
     */
    public function validate($email, Constraint $constraint)
    {
        if (!$constraint instanceof FormEmail) {
            return;
        }

        /** @var Form $form */
        $form = $this->context->getRoot();

        /** @var Customer $customer */
        $customer = $form->getData();

        $accountMode = $this->getAccountMode($form);

        $emailConstraint = new CustomerEmail([
            'shop' => $constraint->getShop(),
            'customerId' => $customer->getId(),
            'accountMode' => $accountMode,
        ]);

        $this->customerEmailValidator->initialize($this->context);
        $this->customerEmailValidator->validate($email, $emailConstraint);

        if ($form->has('emailConfirmation') && $form->get('emailConfirmation')->getData() !== $email) {
            $error = new FormError($this->getSnippet(self::SNIPPET_EMAIL_CONFIRMATION));
            $error->setOrigin($form->get('emailConfirmation'));
            $form->addError($error);
        }
    }

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'FormEmailValidator';
    }

    /**
     * @return int
     */
    private function getAccountMode(FormInterface $form)
    {
        if ($form->has('accountmode')) {
            return $form->get('accountmode')->getData();
        }

        /** @var Customer $customer */
        $customer = $form->getData();

        return $customer->getAccountMode();
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
