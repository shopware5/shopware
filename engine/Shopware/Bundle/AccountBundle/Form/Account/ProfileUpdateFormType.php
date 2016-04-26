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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form reflects the needed fields for changing the email address in the account
 *
 * @package Shopware\Bundle\AccountBundle\Form\Account
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

    /**
     * @param \Shopware_Components_Snippet_Manager $snippetManager
     * @param \Shopware_Components_Config $config
     */
    public function __construct(
        \Shopware_Components_Snippet_Manager $snippetManager,
        \Shopware_Components_Config $config
    ) {
        $this->snippetManager = $snippetManager;
        $this->config = $config;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('salutation', SalutationType::class, [
            'constraints' => [
                new NotBlank(),
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
                'message' => $this->getSnippet(PersonalFormType::SNIPPET_BIRTHDAY)
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
