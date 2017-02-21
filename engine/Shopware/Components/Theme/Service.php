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
namespace Shopware\Components\Theme;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\AbstractQuery;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop as Shop;
use Shopware\Models\Theme\Settings;

/**
 * The theme service class handles all crud operations
 * for the shop templates.
 * It supports to get translated data, nested configuration
 * and shop configuration.
 *
 * @category  Shopware
 * @package   Shopware\Components\Theme
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Service
{
    /**
     * Doctrine entity manager, which used for CRUD operations.
     * @var ModelManager
     */
    private $entityManager;

    /**
     * Snippet manager for translations.
     * @var \Shopware_Components_Snippet_Manager
     */
    private $snippets;

    /**
     * Helper class for theme operations.
     * @var Util
     */
    private $util;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @param ModelManager $entityManager
     * @param \Shopware_Components_Snippet_Manager $snippets
     * @param Util $util
     * @param MediaServiceInterface $mediaService
     */
    public function __construct(
        ModelManager $entityManager,
        \Shopware_Components_Snippet_Manager $snippets,
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
     * @param int $hydration
     * @return Settings|array
     */
    public function getSystemConfiguration($hydration = AbstractQuery::HYDRATE_ARRAY)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select(array('settings'))
            ->from('Shopware\Models\Theme\Settings', 'settings')
            ->orderBy('settings.id', 'ASC')
            ->setFirstResult(0)
            ->setMaxResults(1);

        return $builder->getQuery()->getOneOrNullResult(
            $hydration
        );
    }

    /**
     * Saves the passed configuration data into the database.
     *
     * @param $data
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
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     * @return array
     */
    public function getLayout(Shop\Template $template, Shop\Shop $shop = null)
    {
        $layout = $this->buildConfigLayout(
            $template,
            $shop
        );
        $namespace = $this->getConfigSnippetNamespace($template);
        $namespace->read();

        //theme configurations contains only one main container on the first level.
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
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     * @param array $optionNames
     * @return array
     */
    public function getConfig(Shop\Template $template, Shop\Shop $shop = null, $optionNames = null)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select(array(
            'elements'
        ))
            ->from('Shopware\Models\Shop\TemplateConfig\Element', 'elements')
            ->where('elements.templateId = :templateId')
            ->orderBy('elements.id')
            ->setParameter('templateId', $template->getId());

        if ($shop instanceof Shop\Shop) {
            $builder->addSelect('values')
                ->leftJoin('elements.values', 'values', 'WITH', 'values.shopId = :shopId')
                ->setParameter('shopId', $shop->getId());
        }
        if (!empty($optionNames)) {
            $builder->andWhere('elements.name IN (:optionNames)')
                ->setParameter('optionNames', $optionNames);
        }

        $config = $builder->getQuery()->getArrayResult();

        return $config;
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
     * @param array $container
     * @param Shop\Template $template
     * @param \Enlight_Components_Snippet_Namespace $namespace
     * @return array
     */
    protected function translateContainer(array $container, Shop\Template $template, \Enlight_Components_Snippet_Namespace $namespace)
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

        //recursive call for sub children
        foreach ($container['children'] as &$child) {
            $child = $this->translateContainer($child, $template, $namespace);
        }

        //start recursive translation for the inheritance configuration
        if ($template->getParent() instanceof Shop\Template) {
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
     * @param Shop\Template $template
     * @param Shop\Shop $shop
     * @param null $parentId
     * @return array
     */
    protected function buildConfigLayout(
        Shop\Template $template,
        Shop\Shop $shop = null,
        $parentId = null)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select(array(
            'layout',
            'elements'
        ))
            ->from('Shopware\Models\Shop\TemplateConfig\Layout', 'layout')
            ->leftJoin('layout.elements', 'elements')
            ->where('layout.templateId = :templateId')
            ->orderBy('elements.id')
            ->setParameter('templateId', $template->getId());

        if ($shop instanceof Shop\Shop) {
            $builder->addSelect('values')
                ->leftJoin('elements.values', 'values', 'WITH', 'values.shopId = :shopId')
                ->setParameter('shopId', $shop->getId());
        }

        if ($parentId == null) {
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
     * Returns the configuration sets for the passed template.
     * This function returns additionally the inheritance
     * configuration sets of the passed template.
     * The sets are translated automatically.
     *
     * @param Shop\Template $template
     * @return array
     */
    public function getConfigSets(Shop\Template $template)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select(array(
            'template',
            'sets'
        ))
            ->from('Shopware\Models\Shop\Template', 'template')
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

        if ($template->getParent() instanceof Shop\Template && $instance->useInheritanceConfig()) {
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
     * @param $shopId
     * @param $templateId
     * @throws \Exception
     */
    public function assignShopTemplate($shopId, $templateId)
    {
        /**@var $shop Shop\Shop */
        $shop = $this->entityManager->find('Shopware\Models\Shop\Shop', $shopId);

        if (!$shop instanceof Shop\Shop) {
            throw new \Exception();
        }

        /**@var $template Shop\Template */
        $template = $this->entityManager->find('Shopware\Models\Shop\Template', $templateId);

        if (!$template instanceof Shop\Template) {
            throw new \Exception();
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
     *
     * @param Shop\Template $template
     * @param array $values
     */
    public function saveConfig(Shop\Template $template, array $values)
    {
        foreach ($values as $data) {
            //get the element over the name
            $element = $this->getElementByName(
                $template->getElements(),
                $data['elementName']
            );

            if (!($element instanceof Shop\TemplateConfig\Element)) {
                continue;
            }

            $value = $this->getElementShopValue(
                $element->getValues(),
                $data['shopId']
            );

            /**@var $shop Shop\Shop */
            $shop = $this->entityManager->getReference(
                'Shopware\Models\Shop\Shop',
                $data['shopId']
            );

            if ($element->getType() === 'theme-media-selection') {
                $data['value'] = $this->mediaService->normalize($data['value']);
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
     * @param Shop\Template $template
     * @param array $data
     * @return array
     */
    public function translateTheme(Shop\Template $template, array $data)
    {
        $namespace = $this->getConfigSnippetNamespace($template);
        $namespace->read();

        return $this->translateThemeData($data, $namespace);
    }

    /**
     * Internal helper function which translates the theme meta data.
     *
     * @param array $data
     * @param \Enlight_Components_Snippet_Namespace $namespace
     * @return array
     */
    protected function translateThemeData(array $data, \Enlight_Components_Snippet_Namespace $namespace)
    {
        $data['name'] = $this->convertSnippet($data['name'], $namespace);
        $data['description'] = $this->convertSnippet($data['description'], $namespace);
        $data['author'] = $this->convertSnippet($data['author'], $namespace);
        $data['license'] = $this->convertSnippet($data['license'], $namespace);
        return $data;
    }

    /**
     * Translates the passed config set data.
     *
     * @param $set
     * @param \Enlight_Components_Snippet_Namespace $namespace
     * @return mixed
     */
    public function translateConfigSet($set, \Enlight_Components_Snippet_Namespace $namespace)
    {
        $set['name'] = $this->convertSnippet($set['name'], $namespace);
        $set['description'] = $this->convertSnippet($set['description'], $namespace);
        $set['values'] = $this->translateRecursive($set['values'], $namespace);
        return $set;
    }

    /**
     * Helper function to translate nested arrays recursive.
     *
     * @param $data
     * @param \Enlight_Components_Snippet_Namespace $namespace
     * @return mixed
     */
    private function translateRecursive($data, \Enlight_Components_Snippet_Namespace $namespace)
    {
        if (is_array($data)) {
            foreach ($data as &$value) {
                $value = $this->translateRecursive($value, $namespace);
            }
        } elseif (is_string($data)) {
            $data = $this->convertSnippet($data, $namespace);
        }
        return $data;
    }

    /**
     * Helper function to check, convert and load the translation for
     * the passed value.
     *
     * @param $snippet
     * @param \Enlight_Components_Snippet_Namespace $namespace
     * @return mixed
     */
    private function convertSnippet($snippet, \Enlight_Components_Snippet_Namespace $namespace)
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
     * Checks if the passed value match the snippet pattern.
     *
     * @param $value
     * @return bool
     */
    private function isSnippet($value)
    {
        return (bool)(substr($value, -2) == '__'
            && substr($value, 0, 2) == '__');
    }

    /**
     * Helper function to remove the snippet pattern
     * of the passed snippet name.
     *
     * @param $name
     * @return string
     */
    private function getSnippetName($name)
    {
        $name = substr($name, 2);
        return substr($name, 0, strlen($name) - 2);
    }

    /**
     * Helper function which checks if the element name is already exists in the
     * passed collection of config elements.
     *
     * @param Collection $collection
     * @param string $name
     * @return Shop\TemplateConfig\Element
     */
    private function getElementByName(Collection $collection, $name)
    {
        /**@var $element Shop\TemplateConfig\Element */
        foreach ($collection as $element) {
            if ($element->getName() == $name) {
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
     * @param Collection $collection
     * @param int $shopId
     * @return Shop\TemplateConfig\Value
     */
    private function getElementShopValue(Collection $collection, $shopId)
    {
        /**@var $value Shop\TemplateConfig\Value */
        foreach ($collection as $value) {
            if ($value->getShop() && $value->getShop()->getId() == $shopId) {
                return $value;
            }
        }
        $value = new Shop\TemplateConfig\Value();
        $collection->add($value);
        return $value;
    }

    /**
     * Returns the snippet namespace for the passed template.
     *
     * @param Shop\Template $template
     * @return \Enlight_Components_Snippet_Namespace
     */
    private function getConfigSnippetNamespace(Shop\Template $template)
    {
        return $this->snippets->getNamespace(
            $this->util->getSnippetNamespace($template) . 'backend/config'
        );
    }
}
