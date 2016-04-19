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
use Shopware\Bundle\AccountBundle\Constraint\CurrentPassword;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Form reflects the needed fields for changing the email address in the account
 *
 * @package Shopware\Bundle\AccountBundle\Form\Account
 */
class EmailUpdateFormType extends AbstractType
{
    /**
     * @var \Shopware_Components_Snippet_Manager
     */
    protected $snippetManager;

    /**
     * @var \Shopware_Components_Config
     */
    protected $config;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var ContextServiceInterface
     */
    private $context;

    /**
     * @param \Shopware_Components_Snippet_Manager $snippetManager
     * @param \Shopware_Components_Config $config
     * @param Connection $connection
     * @param Container $container
     * @param ContextServiceInterface $context
     * @internal param ContextServiceInterface $context
     */
    public function __construct(
        \Shopware_Components_Snippet_Manager $snippetManager,
        \Shopware_Components_Config $config,
        Connection $connection,
        Container $container,
        ContextServiceInterface $context
    ) {
        $this->snippetManager = $snippetManager;
        $this->config = $config;
        $this->connection = $connection;
        $this->container = $container;
        $this->context = $context;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('currentPassword', PasswordType::class, [
            'constraints' => $this->getCurrentPasswordConstraints()
        ]);

        $builder->add('email', EmailType::class, [
            'constraints' => $this->getEmailConstraints()
        ]);

        $builder->add('emailConfirmation', EmailType::class, [
            'constraints' => [
                new NotBlank(['message' => null])
            ]
        ]);
    }

    public function getBlockPrefix()
    {
        return 'email';
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

            if ($email != $emailConfirmation) {
                $equalMessage = $this->snippetManager
                    ->getNamespace('frontend/account/internalMessages')
                    ->get('MailFailureNotEqual', 'The mail addresses entered are not equal');

                $context->buildViolation($equalMessage)
                    ->atPath($context->getPropertyPath())
                    ->addViolation();

                $error = new FormError("");
                $error->setOrigin($context->getRoot()->get('emailConfirmation'));
                $context->getRoot()->addError($error);
            }
        };

        $emailUniqueCallback = function ($value, ExecutionContextInterface $context) {
            $shopContext = $this->context->getShopContext();
            $mainShop = $this->container->get('Shop')->getMain() !== null ? $this->container->get('Shop')->getMain() : $this->container->get('Shop');
            $builder = $this->connection->createQueryBuilder();

            $builder->select(1)
                ->from('s_user')
                ->andWhere('email = :email')
                ->andWhere('accountmode != 1')
                ->setParameter('email', $value);

            if ($mainShop->getCustomerScope()) {
                $subshopId = $shopContext->getShop()->getParentId();
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
            new NotBlank(['message' => $emailMessage]),
            new Email(['message' => $emailMessage]),
            new Callback(['callback' => $emailConfirmCallback]),
            new Callback(['callback' => $emailUniqueCallback])
        ];
    }

    /**
     * @return Constraint[]
     */
    private function getCurrentPasswordConstraints()
    {
        $constraints = [];

        if ($this->config->get('accountPasswordCheck')) {
            $constraints[] = new CurrentPassword();
        }

        return $constraints;
    }
}
