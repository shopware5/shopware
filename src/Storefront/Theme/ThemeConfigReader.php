<?php

namespace Shopware\Storefront\Theme;

use Doctrine\DBAL\Connection;

class ThemeConfigReader
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function get(): array
    {
        $builder = $this->connection->createQueryBuilder();

        $builder->select([
            'LOWER(REPLACE(e.name, "_", "")) as name',
            'COALESCE(currentTheme.value, e.default_value) as value',
        ])
            ->from('s_core_templates_config_elements', 'e')
            ->leftJoin('e', 's_core_templates_config_values', 'currentTheme', 'currentTheme.element_id = e.id')
//            ->leftJoin('e', 's_core_templates_config_values', 'parentTheme', 'parentShop.element_id = e.id AND parentTheme.template_id = :parentThemeId')
            ->setParameter('currentThemeId', 23)
//            ->setParameter('parentThemeId', 0)
            //->setParameter('currentThemeId', $theme->getId())
            //->setParameter('parentShopId', $theme->getParentId())
        ;

        $data = $builder->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);

        return $this->hydrate($data);
    }

    public function hydrate(array $config): array
    {
        $config = array_map('unserialize', $config);

        return $config;
    }
}