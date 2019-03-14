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
use Shopware\Bundle\AccountBundle\Constraint\Password;
use Shopware\Models\Attribute\Customer as CustomerAttribute;
use Shopware\Models\Customer\Customer;
use Shopware_Components_Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form reflects the needed fields for changing the password address in the account
 */
class PasswordUpdateFormType extends AbstractType
{
    /**
     * @var Shopware_Components_Config
     */
    protected $config;

    public function __construct(Shopware_Components_Config $config)
    {
        $this->config = $config;
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
        if ($this->config->get('accountPasswordCheck')) {
            $builder->add('currentPassword', PasswordType::class, [
                'mapped' => false,
                'constraints' => [new CurrentPassword()],
            ]);
        }

        $builder->add('password', PasswordType::class, [
            'constraints' => [new Password()],
        ]);

        $builder->add('passwordConfirmation', PasswordType::class, [
            'mapped' => false,
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
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'password';
    }
}
