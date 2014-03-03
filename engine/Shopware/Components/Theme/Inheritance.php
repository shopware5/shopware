<?php

namespace Shopware\Components\Theme;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop as Shop;

class Inheritance
{
    /**
     * @var PathResolver
     */
    private $pathResolver;

    /**
     * @var ModelManager
     */
    private $entityManager;

    function __construct(
        ModelManager $entityManager,
        PathResolver $pathResolver)
    {
        $this->pathResolver = $pathResolver;
        $this->entityManager = $entityManager;
    }

    /**
     * Returns the inheritance hierarchy for the passed theme.
     * @param \Shopware\Models\Shop\Template $template
     * @return \Shopware\Models\Shop\Template[]
     */
    public function getInheritanceHierarchy(Shop\Template $template)
    {
        $hierarchy = array();
        $hierarchy[] = $template;

        if ($template->getParent() instanceof Shop\Template) {
            $hierarchy = array_merge(
                $hierarchy,
                $this->getInheritanceHierarchy($template->getParent())
            );
        }
        return $hierarchy;
    }

    /**
     * Returns the theme directory hierarchy.
     *
     * @param array $hierarchy
     * @return array
     */
    public function getHierarchyPaths(array $hierarchy)
    {
        $directories = array();

        /**@var $theme Shop\Template */
        foreach ($hierarchy as $theme) {
            $directories[] = $this->pathResolver->getDirectory($theme);
        }

        return $directories;
    }

    /**
     * Returns the shop theme configuration for the passed
     * hierarchy.
     * Iterates all passed themes and merges the configuration.
     *
     * @param array $hierarchy
     * @param \Shopware\Models\Shop\Shop $shop
     * @return array
     */
    public function getHierarchyConfig(array $hierarchy, Shop\Shop $shop)
    {
        $config = array();

        /**@var $theme Shop\Template */
        foreach ($hierarchy as $theme) {
            $config = array_merge(
                $themeConfig = $this->getConfig($theme, $shop),
                $config
            );
        }
        return $config;
    }

    /**
     * Registers all smarty functions for each passed
     * shopware theme.
     *
     * @param array $hierarchy
     * @return array
     */
    public function getSmartyDirectories(array $hierarchy)
    {
        $directories = array();

        /**@var $theme Shop\Template */
        foreach ($hierarchy as $theme) {
            $dir = $this->pathResolver->getSmartyDirectory($theme);

            if (!file_exists($dir)) {
                continue;
            }

            $directories[] = $dir;
        }

        return $directories;
    }


    /**
     * Helper function which returns the theme configuration as
     * key - value array.
     * The element name is used as array key, the shop config
     * as value. If no shop config saved, the value will fallback to
     * the default value.
     *
     * @param \Shopware\Models\Shop\Template $template
     * @param \Shopware\Models\Shop\Shop $shop
     * @return array
     */
    private function getConfig(Shop\Template $template, Shop\Shop $shop)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select(array(
            'element.name',
            'values.value',
            'element.defaultValue'
        ));
        $builder->from('Shopware\Models\Shop\TemplateConfig\Element', 'element')
            ->leftJoin('element.values', 'values', 'WITH', 'values.shopId = :shopId')
            ->where('element.templateId = :templateId')
            ->setParameter('shopId', $shop->getId())
            ->setParameter('templateId', $template->getId());

        $data = $builder->getQuery()->getArrayResult();

        foreach ($data as &$row) {
            if (empty($row['value'])) {
                $row['value'] = $row['defaultValue'];
            }
        }

        return array_combine(
            array_column($data, 'name'),
            array_column($data, 'value')
        );
    }
}