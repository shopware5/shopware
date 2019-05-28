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
use Shopware\Models\Customer\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form reflects the needed fields for changing the email address in the account
 */
class ProfileUpdateFormType extends AbstractType
{
    /**
     * @var \Shopware_Components_Snippet_Manager
     */
    protected $snippetManager;

    /**
     * @var \Shopware_Components_Config
     */
    protected $config;

    public function __construct(
        \Shopware_Components_Snippet_Manager $snippetManager,
        \Shopware_Components_Config $config
    ) {
        $this->snippetManager = $snippetManager;
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
        ]);

        $builder->add('attribute', AttributeFormType::class, [
            'data_class' => \Shopware\Models\Attribute\Customer::class,
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'profile';
    }

    /**
     * @return Constraint[]
     */
    private function getBirthdayConstraints()
    {
        $constraints = [];

        if ($this->config->get('showBirthdayField') && $this->config->get('requireBirthdayField')) {
            $constraints[] = new NotBlank([
                'message' => $this->getSnippet(PersonalFormType::SNIPPET_BIRTHDAY),
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
