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

namespace Shopware\Bundle\AccountBundle\Form\Account;

use Shopware\Models\Attribute\Customer as CustomerAttribute;
use Shopware\Bundle\FormBundle\Constraint\Repeated;
use Shopware\Bundle\AccountBundle\Constraint\UniqueEmail;
use Shopware\Bundle\AccountBundle\Type\SalutationType;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware_Components_Snippet_Manager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form reflects the personal fields for the registration, including auth
 *
 * @package Shopware\Bundle\AccountBundle\Form\Account
 */
class PersonalFormType extends AbstractType
{
    const SNIPPET_PASSWORD_CONFIRMATION = [
        'namespace' => 'frontend',
        'name' => 'AccountPasswordNotEqual',
        'default' => 'The passwords are not equal'
    ];

    const SNIPPET_PASSWORD_LENGTH = [
        'namespace' => 'frontend',
        'name' => 'RegisterPasswordLength',
        'default' => ''
    ];

    const SNIPPET_EMAIL_CONFIRMATION = [
        'namespace' => 'frontend/account/internalMessages',
        'name' => 'MailFailureNotEqual',
        'default' => 'The mail addresses entered are not equal'
    ];

    const SNIPPET_MAIL_FAILURE = [
        'namespace' => 'frontend/account/internalMessages',
        'name' => 'MailFailure',
        'default' => 'Please enter a valid mail address'
    ];

    const SNIPPET_BIRTHDAY = [
        'namespace' => 'frontend/account/internalMessages',
        'name' => 'DateFailure',
        'default' => 'Please enter a valid birthday'
    ];

    /**
     * @var Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var ContextServiceInterface
     */
    private $context;

    /**
     * @param Shopware_Components_Snippet_Manager $snippetManager
     * @param \Shopware_Components_Config $config
     * @param ContextServiceInterface $context
     */
    public function __construct(
        Shopware_Components_Snippet_Manager $snippetManager,
        \Shopware_Components_Config $config,
        ContextServiceInterface $context
    ) {
        $this->snippetManager = $snippetManager;
        $this->config = $config;
        $this->context = $context;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class, [
            'constraints' => [new NotBlank(), new Email()]
        ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
            if ($form->has('email')) {
                $form->remove('email');
            }

            $form->add('email', EmailType::class, [
                'constraints' => $this->getEmailConstraints($data['skipLogin'])
            ]);
        });

        if ($this->config->get('doubleemailvalidation')) {
            $builder->add('emailConfirmation', EmailType::class, ['constraints' => [new NotBlank(), new Email()]]);
        }

        $builder->add('password', PasswordType::class, [
            'constraints' => $this->getPasswordConstraints()
        ]);

        if ($this->config->get('doublepasswordvalidation')) {
            $builder->add('passwordConfirmation', PasswordType::class);
        }

        $builder->add('customer_type', TextType::class, [
            'empty_data' => 'private'
        ]);

        $builder->add('salutation', SalutationType::class, [
            'constraints' => [
                new NotBlank()
            ]
        ]);

        $builder->add('title', TextType::class);

        $builder->add('firstname', TextType::class, [
            'constraints' => [
                new NotBlank()
            ]
        ]);

        $builder->add('lastname', TextType::class, [
            'constraints' => [
                new NotBlank()
            ]
        ]);

        $builder->add('birthday', BirthdayType::class, [
            'constraints' => $this->getBirthdayConstraints()
        ]);

        $builder->add('dpacheckbox', TextType::class, [
            'empty_data' => 0,
            'constraints' => $this->getPrivacyConstraints()
        ]);

        $builder->add('attribute', AttributeFormType::class, [
            'data_class' => CustomerAttribute::class
        ]);

        $builder->add('additional', null, [
            'compound' => true,
            'allow_extra_fields' => true
        ]);
    }

    /**
     * @return Constraint[]
     */
    private function getBirthdayConstraints()
    {
        $constraints = [];

        if ($this->config->get('showBirthdayField') && $this->config->get('requireBirthdayField')) {
            $constraints[] = new NotBlank([
                'message' => $this->getSnippet(self::SNIPPET_BIRTHDAY)
            ]);
        }

        return $constraints;
    }

    /**
     * @return Constraint[]
     */
    private function getPrivacyConstraints()
    {
        $constraints = [];

        if ($this->config->get('ACTDPRCHECK')) {
            $constraints[] = new EqualTo(['value' => 1]);
        }

        return $constraints;
    }

    /**
     * @param boolean $skipUnique
     * @return Constraint[]
     */
    private function getEmailConstraints($skipUnique)
    {
        $message = $this->getSnippet(self::SNIPPET_MAIL_FAILURE);

        $constraints = [
            new NotBlank(['message' => $message]),
            new Email(['message' => $message]),
        ];

        if (!$skipUnique) {
            $constraints[] = new UniqueEmail(['shop' => $this->context->getShopContext()->getShop()]);
        }

        if ($this->config->get('doubleemailvalidation')) {
            $constraints[] = new Repeated([
                'field' => 'emailConfirmation',
                'message' => $this->getSnippet(self::SNIPPET_EMAIL_CONFIRMATION)
            ]);
        }

        return $constraints;
    }

    /**
     * @return Constraint[]
     */
    private function getPasswordConstraints()
    {
        $constraints = [
            new Length([
                'min' => $this->config->get('sMINPASSWORD'),
                'minMessage' => $this->getSnippet(self::SNIPPET_PASSWORD_LENGTH)
            ]),
        ];

        if ($this->config->get('doublepasswordvalidation')) {
            $constraints[] = new Repeated([
                'field' => 'passwordConfirmation',
                'message' => $this->getSnippet(self::SNIPPET_PASSWORD_CONFIRMATION)
            ]);
        }

        return $constraints;
    }

    /**
     * @param array $snippet with namespace, name and default value
     * @return string
     */
    private function getSnippet(array $snippet)
    {
        return $this->snippetManager->getNamespace($snippet['namespace'])->get($snippet['name'], $snippet['default'], true);
    }
}
