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

use Shopware\Bundle\AccountBundle\Constraint\FormEmail;
use Shopware\Bundle\AccountBundle\Constraint\Password;
use Shopware\Bundle\AccountBundle\Type\SalutationType;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Models\Attribute\Customer as CustomerAttribute;
use Shopware\Models\Customer\Customer;
use Shopware_Components_Snippet_Manager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form reflects the personal fields for the registration, including auth
 */
class PersonalFormType extends AbstractType
{
    const SNIPPET_BIRTHDAY = [
        'namespace' => 'frontend/account/internalMessages',
        'name' => 'DateFailure',
        'default' => 'Please enter a valid birthday',
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
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'personal';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
            'allow_extra_fields' => true,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $whitelist = [
                'password',
                'passwordConfirmation',
            ];

            $data = $event->getData();

            array_walk_recursive($data, function (&$item, $key) use ($whitelist) {
                if (in_array($key, $whitelist, true)) {
                    return $item;
                }
                $item = strip_tags($item);
            });
            $event->setData($data);
        });

        $builder->add('email', EmailType::class, [
            'constraints' => [
                new FormEmail(['shop' => $this->context->getShopContext()->getShop()]),
            ],
        ]);

        $builder->add('password', PasswordType::class, [
            'constraints' => [new Password()],
        ]);

        if ($this->config->get('doublepasswordvalidation')) {
            $builder->add('passwordConfirmation', PasswordType::class, [
                'mapped' => false,
            ]);
        }

        if ($this->config->get('doubleemailvalidation')) {
            $builder->add('emailConfirmation', EmailType::class, [
                'mapped' => false,
            ]);
        }

        $builder->add('customer_type', TextType::class, [
            'data' => 'private',
        ]);

        $builder->add('salutation', SalutationType::class, [
            'constraints' => [new NotBlank(['message' => null])],
        ]);

        $builder->add('title', TextType::class);

        $builder->add('firstname', TextType::class, [
            'constraints' => [new NotBlank(['message' => null])],
        ]);

        $builder->add('lastname', TextType::class, [
            'constraints' => [new NotBlank(['message' => null])],
        ]);

        $builder->add('birthday', BirthdayType::class, [
            'widget' => $this->config->get('birthdaySingleField') ? 'single_text' : 'choice',
            'constraints' => $this->getBirthdayConstraints(),
            'invalid_message' => $this->getSnippet(self::SNIPPET_BIRTHDAY),
        ]);

        $builder->add('accountmode', TextType::class, [
            'empty_data' => Customer::ACCOUNT_MODE_CUSTOMER,
        ]);

        $builder->add('dpacheckbox', TextType::class, [
            'mapped' => false,
            'empty_data' => 0,
            'constraints' => $this->getPrivacyConstraints(),
        ]);

        $builder->add('attribute', AttributeFormType::class, [
            'data_class' => CustomerAttribute::class,
        ]);

        $builder->add('additional', null, [
            'compound' => true,
            'allow_extra_fields' => true,
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
                'message' => $this->getSnippet(self::SNIPPET_BIRTHDAY),
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
            $constraints[] = new EqualTo([
                'value' => 1,
                'message' => $this->getSnippet(['namespace' => 'frontend/index/privacy', 'name' => 'PrivacyCheckboxError']),
            ]);
        }

        return $constraints;
    }

    /**
     * @param array $snippet with namespace, name and default value
     *
     * @return string
     */
    private function getSnippet(array $snippet)
    {
        return $this->snippetManager->getNamespace($snippet['namespace'])->get($snippet['name'], $snippet['default'], true);
    }
}
