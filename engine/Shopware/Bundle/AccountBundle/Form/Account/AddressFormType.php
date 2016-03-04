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

use Shopware\Bundle\FormBundle\Transformer\EntityTransformer;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Country\Country;
use Shopware\Models\Country\State;
use Shopware_Components_Snippet_Manager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AddressFormType extends AbstractType
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
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var ModelManager
     */
    private $models;

    /**
     * @param Shopware_Components_Snippet_Manager $snippetManager
     * @param \Shopware_Components_Config $config
     * @param \Enlight_Event_EventManager $eventManager
     * @param ModelManager $models
     */
    public function __construct(Shopware_Components_Snippet_Manager $snippetManager, \Shopware_Components_Config $config, \Enlight_Event_EventManager $eventManager, ModelManager $models)
    {
        $this->snippetManager = $snippetManager;
        $this->config = $config;
        $this->eventManager = $eventManager;
        $this->models = $models;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('salutation', TextType::class, [
            'constraints' => [
                new NotBlank(),
                new Choice(['choices' => $this->getSalutationChoices()])
            ]
        ]);

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

        $builder->add('company', TextType::class);

        $builder->add('department', TextType::class);

        $builder->add('vatId', TextType::class, [
            'constraints' => $this->getVatConstraints()
        ]);

        $builder->add('street', TextType::class, [
            'constraints' => [
                new NotBlank()
            ]
        ]);

        $builder->add('zipcode', TextType::class, [
            'constraints' => [
                new NotBlank()
            ]
        ]);

        $builder->add('city', TextType::class, [
            'constraints' => [
                new NotBlank()
            ]
        ]);

        $builder->add('country', IntegerType::class, [
            'constraints' => [
                new NotBlank()
            ]
        ]);

        $builder->add('state', IntegerType::class);

        $builder->add('phone', TextType::class, [
            'constraints' => $this->getPhoneConstraints()
        ]);

        $builder->add('additionalAddressLine1', TextType::class, [
            'constraints' => $this->getAdditionalAddressline1Constraints()
        ]);

        $builder->add('additionalAddressLine2', TextType::class, [
            'constraints' => $this->getAdditionalAddressline2Constraints()
        ]);

        // convert IDs to entities
        $builder->get('country')->addModelTransformer(new EntityTransformer($this->models, Country::class));
        $builder->get('state')->addModelTransformer(new EntityTransformer($this->models, State::class));

        $this->eventManager->notify('Shopware_Form_Account_AddressForm_Builder', ['builder' => $builder]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'address';
    }

    /**
     * @return Constraint[]
     */
    private function getPhoneConstraints()
    {
        $constraints = [];

        if ($this->config->offsetGet('showphonenumberfield') && $this->config->offsetGet('requirePhoneField')) {
            $constraints[] = new NotBlank();
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
            $constraints[] = new NotBlank();
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
            $constraints[] = new NotBlank();
        }

        return $constraints;
    }

    /**
     * @return Constraint[]
     */
    private function getVatConstraints()
    {
        $constraints = [];

        if ($this->config->offsetGet('vatcheckrequired')) {
            $vatCallback = function ($value, ExecutionContextInterface $context) {
                $extraData = $context->getRoot()->getExtraData();

                if (empty($extraData['customer_type']) || $extraData['customer_type'] !== 'business') {
                    return;
                }

                if (!empty($value)) {
                    return;
                }

                $notBlank = new NotBlank();
                $context->buildViolation($notBlank->message)
                    ->atPath($context->getPropertyPath())
                    ->addViolation();


            };

            $constraints[] = new Callback(['callback' => $vatCallback]);
        }

        return $constraints;
    }

    /**
     * @return string[]
     */
    private function getSalutationChoices()
    {
        return ['mr', 'ms'];
    }
}
