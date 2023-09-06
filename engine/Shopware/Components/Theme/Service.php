<?php
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

namespace Shopware\Components\Theme;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\AbstractQuery;
use Enlight_Components_Snippet_Namespace;
use Exception;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Model\Exception\ModelNotFoundException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Shop\Template;
use Shopware\Models\Shop\TemplateConfig\Element;
use Shopware\Models\Shop\TemplateConfig\Layout;
use Shopware\Models\Shop\TemplateConfig\Value;
use Shopware\Models\Theme\Settings;
use Shopware_Components_Snippet_Manager;

/**
 * The theme service class handles all crud operations
 * for the shop templates.
 * It supports to get translated data, nested configuration
 * and shop configuration.
 */
class Service
{
    /**
     * Doctrine entity manager, which used for CRUD operations.
     */
    private ModelManager $entityManager;

    /**
     * Snippet manager for translations.
     */
    private Shopware_Components_Snippet_Manager $snippets;

    /**
     * Helper class for theme operations.
     */
    private Util $util;

    private MediaServiceInterface $mediaService;

    public function __construct(
        ModelManager $entityManager,
        Shopware_Components_Snippet_Manager $snippets,
        Util $util,
        MediaServiceInterface $mediaService
    ) {
        $this->entityManager = $entityManager;
        $this->snippets = $snippets;
        $this->util = $util;
        $this->mediaService = $mediaService;
    }

    /**
     * Returns the system configuration for themes.
     * This configuration is used to configure the less compiler
     * or the js compressor.
     *
     * @phpstan-param AbstractQuery::HYDRATE_* $hydration
     *
     * @param int $hydration
     *
     * @return Settings|array
     */
    public function getSystemConfiguration($hydration = AbstractQuery::HYDRATE_ARRAY)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select(['settings'])
            ->from(Settings::class, 'settings')
            ->orderBy('settings.id', 'ASC')
            ->setFirstResult(0)
            ->setMaxResults(1);

        return $builder->getQuery()->getOneOrNullResult($hydration);
    }

    /**
     * Saves the passed configuration data into the database.
     *
     * @param array $data
     */
    public function saveSystemConfiguration($data)
    {
        $settings = $this->getSystemConfiguration(AbstractQuery::HYDRATE_OBJECT);

        if (!$settings instanceof Settings) {
            $settings = new Settings();
            $this->entityManager->persist($settings);
        }
        $settings->fromArray($data);
        $this->entityManager->flush();
    }

    /**
     * This function returns the nested configuration layout
     * and translate the element and container snippets.
     * If a shop instance passed, the function selects additionally the
     * element values of the passed shop.
     *
     * @param Shop $shop
     *
     * @return array
     */
    public function getLayout(Template $template, ?Shop $shop = null)
    {
        $layout = $this->buildConfigLayout(
            $template,
            $shop
        );
        $namespace = $this->getConfigSnippetNamespace($template);
        $namespace->read();

        // Theme configurations contains only one main container on the first level.
        $layout[0] = $this->translateContainer($layout[0], $template, $namespace);

        return $layout;
    }

    /**
     * This function returns all configuration ids, names and default
     * values for the provided template
     * If a shop is provided, the current values for that shop
     * will also be returned.
     * If provided, only option in $optionNames will be returned
     *
     * @param Shop  $shop
     * @param array $optionNames
     *
     * @return array
     */
    public function getConfig(Template $template, ?Shop $shop = null, $optionNames = null)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select([
            'elements',
        ])
            ->from(Element::class, 'elements')
            ->where('elements.templateId = :templateId')
            ->orderBy('elements.id')
            ->setParameter('templateId', $template->getId());

        if ($shop instanceof Shop) {
            $builder->addSelect('values')
                ->leftJoin('elements.values', 'values', 'WITH', 'values.shopId = :shopId')
                ->setParameter('shopId', $shop->getId());
        }
        if (!empty($optionNames)) {
            $builder->andWhere('elements.name IN (:optionNames)')
                ->setParameter('optionNames', $optionNames);
        }

        return $builder->getQuery()->getArrayResult();
    }

    /**
     * Returns the configuration sets for the passed template.
     * This function returns additionally the inheritance
     * configuration sets of the passed template.
     * The sets are translated automatically.
     *
     * @return array
     */
    public function getConfigSets(Template $template)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select([
            'template',
            'sets',
        ])
            ->from(Template::class, 'template')
            ->innerJoin('template.configSets', 'sets')
            ->where('sets.templateId = :templateId')
            ->orderBy('sets.name')
            ->setParameter('templateId', $template->getId());

        $themes = $builder->getQuery()->getArrayResult();

        $namespace = $this->getConfigSnippetNamespace($template);
        $namespace->read();

        foreach ($themes as &$theme) {
            $theme = $this->translateThemeData($theme, $namespace);

            foreach ($theme['configSets'] as &$set) {
                $set = $this->translateConfigSet($set, $namespace);
            }
        }

        $instance = $this->util->getThemeByTemplate($template);

        if ($template->getParent() instanceof Template && $instance->useInheritanceConfig()) {
            $themes = array_merge(
                $themes,
                $this->getConfigSets(
                    $template->getParent()
                )
            );
        }

        return $themes;
    }

    /**
     * Assigns the passed template id to the passed sub shop.
     *
     * @param int $shopId
     * @param int $templateId
     *
     * @throws Exception
     */
    public function assignShopTemplate($shopId, $templateId)
    {
        $shop = $this->entityManager->find(Shop::class, $shopId);
        if (!$shop instanceof Shop) {
            throw new ModelNotFoundException(Shop::class, $shopId);
        }

        $template = $this->entityManager->find(Template::class, $templateId);
        if (!$template instanceof Template) {
            throw new ModelNotFoundException(Template::class, $templateId);
        }

        $shop->setTemplate($template);

        $this->entityManager->flush();
    }

    /**
     * Saves the passed shop configuration values to the passed
     * template.
     * The configuration elements are identified over the
     * element name.
     * The values array can contains multiple sub shop values,
     * which identified over the shopId parameter inside the values array.
     */
    public function saveConfig(Template $template, array $values)
    {
        foreach ($values as $data) {
            // Get the element using the name
            $element = $this->getElementByName(
                $template->getElements(),
                $data['elementName']
            );

            if (!($element instanceof Element)) {
                continue;
            }

            $value = $this->getElementShopValue(
                $element->getValues(),
                $data['shopId']
            );

            $shop = $this->entityManager->getReference(Shop::class, $data['shopId']);
            if (!$shop instanceof Shop) {
                throw new ModelNotFoundException(Shop::class, $data['shopId']);
            }

            if ($element->getType() === 'theme-media-selection') {
                $data['value'] = $this->mediaService->normalize($data['value']);
            }

            // Don't save default values
            if ($element->getDefaultValue() === $data['value']) {
                $element->getValues()->removeElement($value);
                continue;
            }

            $value->setShop($shop);
            $value->setElement($element);
            $value->setValue($data['value']);
        }

        $this->entityManager->flush();
    }

    /**
     * Translates the theme meta data.
     *
     * @return array
     */
    public function translateTheme(Template $template, array $data)
    {
        $namespace = $this->getConfigSnippetNamespace($template);
        $namespace->read();

        return $this->translateThemeData($data, $namespace);
    }

    /**
     * Translates the passed config set data.
     *
     * @param array $set
     *
     * @return array
     */
    public function translateConfigSet($set, Enlight_Components_Snippet_Namespace $namespace)
    {
        $set['name'] = $this->convertSnippet($set['name'], $namespace);
        $set['description'] = $this->convertSnippet($set['description'], $namespace);
        $set['values'] = $this->translateRecursive($set['values'], $namespace);

        return $set;
    }

    /**
     * Translates the passed container values.
     *
     * This function is a double recursive function.
     * The function iterates first the container elements
     * and children to translate the configuration with the
     * current namespace.
     * After the container should be translated with the
     * current namespace, the function needs to load
     * the template parent namespace and calls himself again.
     * This is required because the theme configuration are copied
     * from the extended theme but the snippets are not copied.
     *
     * @return array
     */
    protected function translateContainer(array $container, Template $template, Enlight_Components_Snippet_Namespace $namespace)
    {
        foreach ($container['elements'] as &$element) {
            $element['fieldLabel'] = $this->convertSnippet(
                $element['fieldLabel'],
                $namespace
            );

            $element['supportText'] = $this->convertSnippet(
                $element['supportText'],
                $namespace
            );

            $element['help'] = $this->convertSnippet(
                $element['help'],
                $namespace
            );

            $element['defaultValue'] = $this->convertSnippet(
                $element['defaultValue'],
                $namespace
            );

            if ($element['attributes']) {
                $element['attributes']['supportText'] = $this->convertSnippet(
                    $element['attributes']['supportText'],
                    $namespace
                );

                $element['attributes']['helpText'] = $this->convertSnippet(
                    $element['attributes']['helpText'],
                    $namespace
                );

                $element['attributes']['boxLabel'] = $this->convertSnippet(
                    $element['attributes']['boxLabel'],
                    $namespace
                );
            }

            if (isset($element['selection'])) {
                foreach ($element['selection'] as &$selection) {
                    foreach ($selection as &$value) {
                        $value = $this->convertSnippet($value, $namespace);
                    }
                }
            }
        }

        $container['title'] = $this->convertSnippet(
            $container['title'],
            $namespace
        );

        // Recursive call for sub children
        foreach ($container['children'] as &$child) {
            $child = $this->translateContainer($child, $template, $namespace);
        }

        // Start recursive translation for the inheritance configuration
        if ($template->getParent() instanceof Template) {
            $parentNamespace = $this->getConfigSnippetNamespace($template->getParent());
            $namespace->read();
            $container = $this->translateContainer($container, $template->getParent(), $parentNamespace);
        }

        return $container;
    }

    /**
     * This function reads out the nested configuration layout
     * and translate the element and container snippets.
     * If a shop instance passed, the function selects additionally the
     * element values of the passed shop.
     *
     * @param int|null $parentId
     *
     * @return array
     */
    protected function buildConfigLayout(
        Template $template,
        ?Shop $shop = null,
        $parentId = null
    ) {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select([
            'layout',
            'elements',
        ])
            ->from(Layout::class, 'layout')
            ->leftJoin('layout.elements', 'elements')
            ->where('layout.templateId = :templateId')
            ->orderBy('elements.id')
            ->setParameter('templateId', $template->getId());

        if ($shop instanceof Shop) {
            $builder->addSelect('values')
                ->leftJoin('elements.values', 'values', 'WITH', 'values.shopId = :shopId')
                ->setParameter('shopId', $shop->getId());
        }

        if ($parentId === null) {
            $builder->andWhere('layout.parentId IS NULL');
        } else {
            $builder->andWhere('layout.parentId = :parentId')
                ->setParameter('parentId', $parentId);
        }

        $layout = $builder->getQuery()->getArrayResult();

        foreach ($layout as &$container) {
            $container['children'] = $this->buildConfigLayout(
                $template,
                $shop,
                $container['id']
            );
        }

        return $layout;
    }

    /**
     * Internal helper function which translates the theme meta data.
     *
     * @return array
     */
    protected function translateThemeData(array $data, Enlight_Components_Snippet_Namespace $namespace)
    {
        $data['name'] = $this->convertSnippet($data['name'], $namespace);
        $data['description'] = $this->convertSnippet($data['description'], $namespace);
        $data['author'] = $this->convertSnippet($data['author'], $namespace);
        $data['license'] = $this->convertSnippet($data['license'], $namespace);

        return $data;
    }

    /**
     * Helper function to translate nested arrays recursive.
     *
     * @param string|array $data
     */
    private function translateRecursive($data, Enlight_Components_Snippet_Namespace $namespace)
    {
        if (\is_array($data)) {
            foreach ($data as &$value) {
                $value = $this->translateRecursive($value, $namespace);
            }
        } elseif (\is_string($data)) {
            $data = $this->convertSnippet($data, $namespace);
        }

        return $data;
    }

    /**
     * Helper function to check, convert and load the translation for
     * the passed value.
     */
    private function convertSnippet(?string $snippet, Enlight_Components_Snippet_Namespace $namespace): ?string
    {
        if (!$this->isSnippet($snippet)) {
            return $snippet;
        }

        return $namespace->get(
            $this->getSnippetName($snippet),
            $snippet
        );
    }

    /**
     * Checks if the passed value match the snippet pattern
     */
    private function isSnippet(?string $value): bool
    {
        if ($value === null) {
            return false;
        }

        return str_ends_with($value, '__') && str_starts_with($value, '__');
    }

    /**
     * Helper function to remove the snippet pattern
     */
    private function getSnippetName(string $name): string
    {
        $name = substr($name, 2);

        return substr($name, 0, -2);
    }

    /**
     * Helper function which checks if the element name is already exists in the
     * passed collection of config elements.
     *
     * @param ArrayCollection<array-key, Element> $collection
     */
    private function getElementByName(Collection $collection, string $name): ?Element
    {
        foreach ($collection as $element) {
            if ($element->getName() === $name) {
                return $element;
            }
        }

        return null;
    }

    /**
     * Helper function to get the theme configuration value of the passed
     * value collection.
     * If no shop value exist, the function creates a new value object.
     *
     * @param ArrayCollection<array-key, Value> $collection
     */
    private function getElementShopValue(Collection $collection, int $shopId): Value
    {
        foreach ($collection as $value) {
            if ($value->getShop() && $value->getShop()->getId() == $shopId) {
                return $value;
            }
        }
        $value = new Value();
        $collection->add($value);

        return $value;
    }

    /**
     * Returns the snippet namespace for the passed template.
     */
    private function getConfigSnippetNamespace(Template $template): Enlight_Components_Snippet_Namespace
    {
        return $this->snippets->getNamespace($this->util->getSnippetNamespace($template) . 'backend/config');
    }
}
