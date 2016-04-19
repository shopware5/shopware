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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\AccountBundle\Type\SalutationType;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\DependencyInjection\Container;
use Shopware_Components_Snippet_Manager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Form reflects the personal fields for the registration, including auth
 *
 * @package Shopware\Bundle\AccountBundle\Form\Account
 */
class PersonalFormType extends AbstractType
{
    /**
     * @var Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ContextServiceInterface
     */
    private $context;

    /**
     * @var Container
     */
    private $container;

    /**
     * @param Shopware_Components_Snippet_Manager $snippetManager
     * @param \Shopware_Components_Config $config
     * @param Connection $connection
     * @param ContextServiceInterface $context
     * @param Container $container
     */
    public function __construct(
        Shopware_Components_Snippet_Manager $snippetManager,
        \Shopware_Components_Config $config,
        Connection $connection,
        ContextServiceInterface $context,
        Container $container
    ) {
        $this->snippetManager = $snippetManager;
        $this->config = $config;
        $this->connection = $connection;
        $this->context = $context;
        $this->container = $container;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class, [
            'constraints' => $this->getEmailConstraints()
        ]);

        $builder->add('password', PasswordType::class, [
            'constraints' => $this->getPasswordConstraints()
        ]);

        $builder->add('emailConfirmation', EmailType::class);
        $builder->add('passwordConfirmation', PasswordType::class);

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
    }

    /**
     * @return Constraint[]
     */
    private function getBirthdayConstraints()
    {
        $constraints = [];

        if ($this->config->get('showBirthdayField') && $this->config->get('requireBirthdayField')) {
            $birthdayMessage = $this->snippetManager
                ->getNamespace('frontend/account/internalMessages')
                ->get('DateFailure', 'Please enter a valid birthday');

            $constraints[] = new NotBlank(['message' => $birthdayMessage]);
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
     * @return Constraint[]
     */
    private function getEmailConstraints()
    {
        $emailConfirmCallback = function ($value, ExecutionContextInterface $context) {

            $data = $context->getRoot()->getData();

            $email = trim(strtolower($data['email']));
            $emailConfirmation = trim(strtolower($data['emailConfirmation']));

            if ($this->config->get('doubleemailvalidation') && $email != $emailConfirmation) {
                $equalMessage = $this->snippetManager
                    ->getNamespace('frontend/account/internalMessages')
                    ->get('MailFailureNotEqual', 'The mail addresses entered are not equal');

                $context->buildViolation($equalMessage)
                    ->atPath('emailConfirmation')
                    ->addViolation();
            }
        };

        $emailUniqueCallback = function ($value, ExecutionContextInterface $context) {

            $extraData = $context->getRoot()->getExtraData();

            if (!empty($extraData['skipLogin'])) {
                return;
            }

            $mainShop = $this->container->get('Shop')->getMain() !== null ? $this->container->get('Shop')->getMain() : $this->container->get('Shop');

            $builder = $this->connection->createQueryBuilder();
            $builder->select(1)
                ->from('s_user')
                ->andWhere('email = :email')
                ->andWhere('accountmode != 1')
                ->setParameter('email', $value);

            if ($mainShop->getCustomerScope()) {
                $subshopId = $this->context->getShopContext()->getShop()->getParentId();
                $builder
                    ->andWhere('subshopID = :subshopId')
                    ->setParameter('subshopId', $subshopId);
            }

            $exists = $builder->execute()->rowCount() > 0;

            if ($exists) {
                $emailMessage = $this->snippetManager
                    ->getNamespace('frontend/account/internalMessages')
                    ->get('MailFailureAlreadyRegistered', 'This mail address is already registered');

                $context->buildViolation($emailMessage)
                    ->atPath($context->getPropertyPath())
                    ->addViolation();
            }

        };

        $emailMessage = $this->snippetManager
            ->getNamespace('frontend/account/internalMessages')
            ->get('MailFailure', 'Please enter a valid mail address');

        return [
            new NotBlank(),
            new Email(['message' => $emailMessage]),
            new Callback(['callback' => $emailConfirmCallback]),
            new Callback(['callback' => $emailUniqueCallback])
        ];
    }

    /**
     * @return Constraint[]
     */
    private function getPasswordConstraints()
    {
        $minMessage = $this->snippetManager
            ->getNamespace("frontend")
            ->get('RegisterPasswordLength', '', true);

        $passwordEqualCallback = function ($value, ExecutionContextInterface $context) {
            $data = $context->getRoot()->getData();

            if ($this->config->get('doublepasswordvalidation') && $data['password'] != $data['passwordConfirmation']) {
                $equalMessage = $this->snippetManager
                    ->getNamespace("frontend")
                    ->get('AccountPasswordNotEqual', 'The passwords are not equal', true);

                $context->buildViolation($equalMessage)
                    ->atPath($context->getPropertyPath())
                    ->addViolation();
            }
        };

        return [
            new Length(['min' => $this->config->get('sMINPASSWORD'), 'minMessage' => $minMessage]),
            new Callback(['callback' => $passwordEqualCallback])
        ];
    }
}
