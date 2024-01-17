<?php

declare(strict_types=1);
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

namespace Shopware\Bundle\AccountBundle\Form\Account;

use Shopware\Bundle\AccountBundle\Type\SalutationType;
use Shopware\Bundle\FormBundle\Transformer\EntityTransformer;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Attribute\CustomerAddress as AddressAttribute;
use Shopware\Models\Country\Country;
use Shopware\Models\Country\State;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Shopware_Components_Config;
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

/**
 * @extends AbstractType<Address>
 */
class AddressFormType extends AbstractType
{
    private Shopware_Components_Config $config;

    private ModelManager $models;

    public function __construct(Shopware_Components_Config $config, ModelManager $models)
    {
        $this->config = $config;
        $this->models = $models;
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            array_walk_recursive($data, static function (&$item) {
                $item = strip_tags((string) $item);
            });
            $event->setData($data);
        });

        if ($this->config->get('shopSalutationRequired')) {
            $builder->add('salutation', SalutationType::class, [
                'constraints' => [new NotBlank(['message' => null])],
            ]);
        }

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
            'constraints' => $this->getAdditionalAddressLine1Constraints(),
        ]);

        $builder->add('additionalAddressLine2', TextType::class, [
            'constraints' => $this->getAdditionalAddressLine2Constraints(),
        ]);

        // convert IDs to entities
        $builder->get('country')->addModelTransformer(new EntityTransformer($this->models, Country::class));
        $builder->get('state')->addModelTransformer(new EntityTransformer($this->models, State::class));

        $builder->add('attribute', AttributeFormType::class, [
            'data_class' => AddressAttribute::class,
        ]);

        // dynamic field which contains multiple values
        // used for extendable data which has not to persist over attributes
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

        if ($this->config->get('vatcheckrequired')) {
            $this->addVatIdValidation($builder);
        }
    }

    /**
     * @return list<Constraint>
     */
    private function getPhoneConstraints(): array
    {
        $constraints = [];

        if ($this->config->get('showphonenumberfield') && $this->config->get('requirePhoneField')) {
            $constraints[] = new NotBlank(['message' => null]);
        }

        return $constraints;
    }

    /**
     * @return list<Constraint>
     */
    private function getAdditionalAddressLine1Constraints(): array
    {
        $constraints = [];

        if ($this->config->get('showAdditionAddressLine1') && $this->config->get('requireAdditionAddressLine1')) {
            $constraints[] = new NotBlank(['message' => null]);
        }

        return $constraints;
    }

    /**
     * @return list<Constraint>
     */
    private function getAdditionalAddressLine2Constraints(): array
    {
        $constraints = [];

        if ($this->config->get('showAdditionAddressLine2') && $this->config->get('requireAdditionAddressLine2')) {
            $constraints[] = new NotBlank(['message' => null]);
        }

        return $constraints;
    }

    /**
     * @param FormBuilderInterface<Address|null> $builder
     */
    private function addCompanyValidation(FormBuilderInterface $builder): void
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

    /**
     * @param FormBuilderInterface<Address|null> $builder
     */
    private function addVatIdValidation(FormBuilderInterface $builder): void
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

    /**
     * @param FormBuilderInterface<Address|null> $builder
     */
    private function addCountryStateValidation(FormBuilderInterface $builder): void
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
