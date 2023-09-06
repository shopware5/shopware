<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\AccountBundle\Constraint;

use Shopware\Models\Customer\Customer;
use Shopware_Components_Config as Config;
use Shopware_Components_Snippet_Manager as SnippetManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PasswordValidator extends ConstraintValidator
{
    public const SNIPPET_PASSWORD_CONFIRMATION = [
        'namespace' => 'frontend',
        'name' => 'AccountPasswordNotEqual',
        'default' => 'The passwords are not equal',
    ];

    public const SNIPPET_PASSWORD_LENGTH = [
        'namespace' => 'frontend',
        'name' => 'RegisterPasswordLength',
        'default' => '',
    ];

    private SnippetManager $snippets;

    private Config $config;

    public function __construct(
        SnippetManager $snippets,
        Config $config
    ) {
        $this->snippets = $snippets;
        $this->config = $config;
    }

    /**
     * @param string $password
     */
    public function validate($password, Constraint $constraint)
    {
        if (!$constraint instanceof Password) {
            return;
        }

        /** @var Form $form */
        $form = $this->context->getRoot();

        if ($this->isFastLogin($form)) {
            return;
        }

        $minLength = (int) $this->config->get('minPassword');

        if (empty($password) || ($minLength && \strlen($password) < $minLength)) {
            $this->addError($this->getSnippet(self::SNIPPET_PASSWORD_LENGTH));
        }

        if ($form->has('passwordConfirmation') && $form->get('passwordConfirmation')->getData() !== $password) {
            $error = new FormError($this->getSnippet(self::SNIPPET_PASSWORD_CONFIRMATION));
            $error->setOrigin($form->get('passwordConfirmation'));
            $form->addError($error);
        }
    }

    private function addError(string $message): void
    {
        $this->context->buildViolation($message)
            ->atPath($this->context->getPropertyPath())
            ->addViolation();
    }

    /**
     * @param FormInterface<Customer> $form
     */
    private function isFastLogin(FormInterface $form): bool
    {
        if ($form->has('accountmode')) {
            return $form->get('accountmode')->getData() == Customer::ACCOUNT_MODE_FAST_LOGIN;
        }

        $customer = $form->getData();
        if ($customer instanceof Customer) {
            return $customer->getAccountMode() == Customer::ACCOUNT_MODE_FAST_LOGIN;
        }

        return false;
    }

    /**
     * @param array $snippet A snippet with namespace, name and default value
     */
    private function getSnippet(array $snippet): string
    {
        return $this->snippets->getNamespace($snippet['namespace'])->get($snippet['name'], $snippet['default'], true);
    }
}
