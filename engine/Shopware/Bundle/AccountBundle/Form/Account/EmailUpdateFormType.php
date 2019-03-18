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

use Shopware\Bundle\AccountBundle\Constraint\CurrentPassword;
use Shopware\Bundle\AccountBundle\Constraint\FormEmail;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Models\Attribute\Customer as CustomerAttribute;
use Shopware\Models\Customer\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form reflects the needed fields for changing the email address in the account
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
     * @var ContextServiceInterface
     */
    private $context;

    public function __construct(
        \Shopware_Components_Snippet_Manager $snippetManager,
        \Shopware_Components_Config $config,
        ContextServiceInterface $context
    ) {
        $this->snippetManager = $snippetManager;
        $this->config = $config;
        $this->context = $context;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
            'allow_extra_fields' => true,
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'email';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class, [
            'constraints' => [
                new FormEmail(['shop' => $this->context->getShopContext()->getShop()]),
            ],
        ]);

        $builder->add('emailConfirmation', EmailType::class, [
            'mapped' => false,
        ]);

        if ($this->config->get('accountPasswordCheck')) {
            $builder->add('currentPassword', PasswordType::class, [
                'mapped' => false,
                'constraints' => [new CurrentPassword()],
            ]);
        }

        $builder->add('attribute', AttributeFormType::class, [
            'data_class' => CustomerAttribute::class,
        ]);

        $builder->add('additional', null, [
            'compound' => true,
            'allow_extra_fields' => true,
        ]);
    }
}
