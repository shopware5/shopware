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

namespace Shopware\Components\Plugin;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Config\ElementTranslation;
use Shopware\Models\Config\Form;
use Shopware\Models\Config\FormTranslation;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Locale;

class FormSynchronizer
{
    /**
     * @var ModelManager
     */
    private $em;

    /**
     * @var ModelRepository
     */
    private $formRepository;

    /**
     * @var ModelRepository
     */
    private $localeRepository;

    public function __construct(ModelManager $em)
    {
        $this->em = $em;
        $this->formRepository = $this->em->getRepository(Form::class);
        $this->localeRepository = $this->em->getRepository(Locale::class);
    }

    public function synchronize(Plugin $plugin, array $config)
    {
        $form = $this->getForm($plugin);

        $translations = [];

        foreach ($config['elements'] as $key => $element) {
            $options = [
                'scope' => $element['scope'],
                'label' => $element['label']['en'],
                'value' => $element['value'] ?? null,
                'required' => $element['isRequired'],
                'position' => $key,
            ] + $element['options'];

            if (isset($element['label'])) {
                foreach ($element['label'] as $lang => $text) {
                    $translations[$lang][$element['name']]['label'] = $text;
                }
            }

            if (isset($element['description'])) {
                foreach ($element['description'] as $lang => $text) {
                    $translations[$lang][$element['name']]['description'] = $text;
                }
            }

            if (isset($element['store'])) {
                $options['store'] = $element['store'];
            }

            if (isset($element['description']['en'])) {
                $options['description'] = $element['description']['en'];
            }

            if ($element['type'] === 'password') {
                $element['type'] = 'text';
                $options['inputType'] = 'password';
            }

            $form->setElement($element['type'], $element['name'], $options);
        }

        if (isset($config['description'])) {
            foreach ($config['description'] as $lang => $text) {
                $translations[$lang]['plugin_form']['description'] = $text;
            }
        }

        if (isset($config['label'])) {
            foreach ($config['label'] as $lang => $text) {
                $translations[$lang]['plugin_form']['label'] = $text;
            }
        }

        $this->addFormTranslations($translations, $form);

        $this->em->flush();

        $this->removeNotExistingElements($plugin, array_column($config['elements'], 'name'));
    }

    /**
     * Removes no more existing form elements and their translations
     *
     * @param string[] $names
     */
    private function removeNotExistingElements(Plugin $plugin, array $names)
    {
        $query = $this->em->getConnection()->createQueryBuilder();
        $query->select('elements.id');
        $query->from('s_core_config_elements', 'elements');
        $query->innerJoin('elements', 's_core_config_forms', 'form', 'elements.form_id = form.id AND form.plugin_id = :pluginId');
        $query->where('elements.name NOT IN (:names)');
        $query->setParameter(':names', $names, Connection::PARAM_STR_ARRAY);
        $query->setParameter(':pluginId', $plugin->getId());

        $ids = $query->execute()->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($ids)) {
            return;
        }

        //elements
        $query = $this->em->getConnection()->createQueryBuilder();
        $query->delete('s_core_config_elements');
        $query->where('id IN (:ids)');
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);
        $query->execute();

        //saved values
        $query = $this->em->getConnection()->createQueryBuilder();
        $query->delete('s_core_config_values');
        $query->where('element_id IN (:ids)');
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);
        $query->execute();

        //translations
        $query = $this->em->getConnection()->createQueryBuilder();
        $query->delete('s_core_config_element_translations');
        $query->where('element_id IN (:ids)');
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);
        $query->execute();
    }

    /**
     * Returns plugin form
     *
     * @return Form
     */
    private function getForm(Plugin $plugin)
    {
        /** @var Form|null $form */
        $form = $this->formRepository->findOneBy([
            'pluginId' => $plugin->getId(),
        ]);

        if (!$form) {
            $form = $this->initForm($plugin);
        }

        return $form;
    }

    /**
     * @return Form
     */
    private function initForm(Plugin $plugin)
    {
        $form = new Form();
        $form->setPluginId($plugin->getId());

        $form->setName($plugin->getName());
        $form->setLabel($plugin->getLabel());
        $form->setDescription($plugin->getDescription());

        /** @var Form $parent */
        $parent = $this->formRepository->findOneBy([
            'name' => strpos($plugin->getName(), 'Payment') !== false ? 'Payment' : 'Other',
        ]);

        $form->setParent($parent);
        $this->em->persist($form);

        return $form;
    }

    /**
     * Adds translations to the form and its elements. The accepted array format
     * accepts a special 'plugin_form' key for the form translation. All other
     * keys will be matched to element names.
     *
     * Example $translations array:
     * <code>
     * array(
     * 'en_GB' => array(
     * 'plugin_form' => array(
     * 'label' => 'Recently viewed items'
     * ),
     * 'show' => array(
     * 'label' => 'Display recently viewed items'
     * ),
     * 'thumb' => array(
     * 'label' => 'Thumbnail size',
     * 'description' => 'Index of the thumbnail size of the associated album to use. Starts at 0'
     * )
     * )
     * )
     * </code>
     */
    private function addFormTranslations(array $translations, Form $form)
    {
        foreach ($translations as $localeCode => $translationSet) {
            if ($localeCode === 'de') {
                $localeCode = 'de_DE';
            }

            if ($localeCode === 'en') {
                $localeCode = 'en_GB';
            }

            /** @var Locale $locale */
            $locale = $this->localeRepository->findOneBy(['locale' => $localeCode]);
            if (empty($locale)) {
                continue;
            }

            if (isset($translationSet['plugin_form'])) {
                $this->addFormTranslation($form, $translationSet['plugin_form'], $locale);
                unset($translationSet['plugin_form']);
            }

            // Then the element translations
            foreach ($translationSet as $targetName => $translationArray) {
                $isUpdate = false;
                $element = $form->getElement($targetName);
                foreach ($element->getTranslations() as $existingTranslation) {
                    // Check if translation for this locale already exists
                    if ($existingTranslation->getLocale()->getLocale() != $locale->getLocale()) {
                        continue;
                    }

                    if (array_key_exists('label', $translationArray)) {
                        $existingTranslation->setLabel($translationArray['label']);
                    }

                    if (array_key_exists('description', $translationArray)) {
                        $existingTranslation->setDescription($translationArray['description']);
                    }
                    $isUpdate = true;
                    break;
                }

                if (!$isUpdate) {
                    $elementTranslation = new ElementTranslation();
                    if (array_key_exists('label', $translationArray)) {
                        $elementTranslation->setLabel($translationArray['label']);
                    }
                    if (array_key_exists('description', $translationArray)) {
                        $elementTranslation->setDescription($translationArray['description']);
                    }
                    $elementTranslation->setLocale($locale);
                    $element->addTranslation($elementTranslation);
                }
            }
        }
    }

    private function addFormTranslation(Form $form, array $translationArray, Locale $locale)
    {
        $isUpdate = false;
        foreach ($form->getTranslations() as $existingTranslation) {
            // Check if translation for this locale already exists
            if ($existingTranslation->getLocale()->getLocale() != $locale->getLocale()) {
                continue;
            }
            if (array_key_exists('label', $translationArray)) {
                $existingTranslation->setLabel($translationArray['label']);
            }
            if (array_key_exists('description', $translationArray)) {
                $existingTranslation->setDescription($translationArray['description']);
            }
            $isUpdate = true;
            break;
        }
        if (!$isUpdate) {
            $formTranslation = new FormTranslation();
            if (array_key_exists('label', $translationArray)) {
                $formTranslation->setLabel($translationArray['label']);
            }
            if (array_key_exists('description', $translationArray)) {
                $formTranslation->setDescription($translationArray['description']);
            }
            $formTranslation->setLocale($locale);
            $form->addTranslation($formTranslation);
        }
    }
}
