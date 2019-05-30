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

use Shopware\Bundle\AccountBundle\Type\SalutationType;
use Shopware\Bundle\FormBundle\Transformer\EntityTransformer;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Attribute\CustomerAddress as AddressAttribute;
use Shopware\Models\Country\Country;
use Shopware\Models\Country\State;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddressFormType extends AbstractType
{
    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var ModelManager
     */
    private $models;

    public function __construct(\Shopware_Components_Config $config, ModelManager $models)
    {
        $this->config = $config;
        $this->models = $models;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
            'allow_extra_fields' => true,
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'address';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            array_walk_recursive($data, function (&$item) {
                $item = strip_tags($item);
            });
            $event->setData($data);
        });

        $builder->add('salutation', SalutationType::class, [
            'constraints' => [new NotBlank(['message' => null])],
        ]);

        $builder->add('firstname', TextType::class, [
            'constraints' => [new NotBlank(['message' => null])],
        ]);

        $builder->add('lastname', TextType::class, [
            'constraints' => [new NotBlank(['message' => null])],
        ]);

        $builder->add('title', TextType::class);
        $builder->add('company', TextType::class);
        $builder->add('department', TextType::class);
        $builder->add('vatId', TextType::class);

        $builder->add('street', TextType::class, [
            'constraints' => [new NotBlank(['message' => null])],
        ]);

        $builder->add('zipcode', TextType::class, [
            'constraints' => [new NotBlank(['message' => null])],
        ]);

        $builder->add('city', TextType::class, [
            'constraints' => [new NotBlank(['message' => null])],
        ]);

        $builder->add('country', IntegerType::class, [
            'constraints' => [new NotBlank(['message' => null])],
        ]);
        $builder->add('state', IntegerType::class);

        $builder->add('phone', TextType::class, [
            'constraints' => $this->getPhoneConstraints(),
        ]);

        $builder->add('additionalAddressLine1', TextType::class, [
            'constraints' => $this->getAdditionalAddressline1Constraints(),
        ]);

        $builder->add('additionalAddressLine2', TextType::class, [
            'constraints' => $this->getAdditionalAddressline2Constraints(),
        ]);

        // convert IDs to entities
        $builder->get('country')->addModelTransformer(new EntityTransformer($this->models, Country::class));
        $builder->get('state')->addModelTransformer(new EntityTransformer($this->models, State::class));

        $builder->add('attribute', AttributeFormType::class, [
            'data_class' => AddressAttribute::class,
        ]);

        //dynamic field which contains multiple values
        //used for extendable data which has not to persist over attributes
        $builder->add('additional', null, [
            'compound' => true,
            'allow_extra_fields' => true,
        ]);

        // default additional fields
        $builder
            ->get('additional')
            ->add('customer_type', TextType::class, ['required' => false, 'data' => 'private'])
            ->add('setDefaultBillingAddress', CheckboxType::class, ['required' => false, 'data' => false])
            ->add('setDefaultShippingAddress', CheckboxType::class, ['required' => false, 'data' => false])
        ;

        $this->addCountryStateValidation($builder);
        $this->addCompanyValidation($builder);

        if ($this->config->offsetGet('vatcheckrequired')) {
            $this->addVatIdValidation($builder);
        }
    }

    /**
     * @return Constraint[]
     */
    private function getPhoneConstraints()
    {
        $constraints = [];

        if ($this->config->offsetGet('showphonenumberfield') && $this->config->offsetGet('requirePhoneField')) {
            $constraints[] = new NotBlank(['message' => null]);
        }

        return $constraints;
    }

    /**
     * @return Constraint[]
     */
    private function getAdditionalAddressline1Constraints()
    {
        $constraints = [];

        if ($this->config->offsetGet('showAdditionAddressLine1') && $this->config->offsetGet('requireAdditionAddressLine1')) {
            $constraints[] = new NotBlank(['message' => null]);
        }

        return $constraints;
    }

    /**
     * @return Constraint[]
     */
    private function getAdditionalAddressline2Constraints()
    {
        $constraints = [];

        if ($this->config->offsetGet('showAdditionAddressLine2') && $this->config->offsetGet('requireAdditionAddressLine2')) {
            $constraints[] = new NotBlank(['message' => null]);
        }

        return $constraints;
    }

    private function addCompanyValidation(FormBuilderInterface $builder)
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();

            /** @var Address $data */
            $data = $form->getData();
            $customerType = $form->get('additional')->get('customer_type')->getData();

            if ($customerType !== Customer::CUSTOMER_TYPE_BUSINESS || !empty($data->getCompany())) {
                return;
            }

            $notBlank = new NotBlank(['message' => null]);
            $error = new FormError($notBlank->message);
            $error->setOrigin($form->get('company'));
            $form->addError($error);
        });
    }

    private function addVatIdValidation(FormBuilderInterface $builder)
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();

            /** @var Address $data */
            $data = $form->getData();
            $customerType = $form->get('additional')->get('customer_type')->getData();

            if ($customerType !== Customer::CUSTOMER_TYPE_BUSINESS || !empty($data->getVatId())) {
                return;
            }

            $notBlank = new NotBlank(['message' => null]);
            $error = new FormError($notBlank->message);
            $error->setOrigin($form->get('vatId'));
            $form->addError($error);
        });
    }

    private function addCountryStateValidation(FormBuilderInterface $builder)
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();

            /** @var Address $data */
            $data = $event->getData();

            if ($data->getCountry() && $data->getCountry()->getForceStateInRegistration() && !$data->getState()) {
                $notBlank = new NotBlank(['message' => null]);
                $error = new FormError($notBlank->message);
                $error->setOrigin($form->get('state'));
                $form->addError($error);
            }
        });
    }
}
