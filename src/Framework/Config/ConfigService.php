<?php
declare(strict_types=1);

namespace Shopware\Framework\Config;

use Doctrine\DBAL\Connection;
use Shopware\Framework\Framework;

class ConfigService implements ConfigServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getByShop(array $shop): array
    {
        $builder = $this->connection->createQueryBuilder();

        $builder->select([
                'e.name',
                'COALESCE(currentShop.value, parentShop.value, fallbackShop.value, e.value) as value',
            ])
            ->from('s_core_config_elements', 'e')
            ->leftJoin('e', 's_core_config_values', 'currentShop', 'currentShop.element_id = e.id AND currentShop.shop_id = :currentShopId')
            ->leftJoin('e', 's_core_config_values', 'parentShop', 'parentShop.element_id = e.id AND parentShop.shop_id = :parentShopId')
            ->leftJoin('e', 's_core_config_values', 'fallbackShop', 'fallbackShop.element_id = e.id AND fallbackShop.shop_id = :fallbackShopId')
            ->leftJoin('e', 's_core_config_forms', 'forms', 'forms.id = e.form_id')
            ->setParameter('fallbackShopId', 1)
            ->setParameter('currentShopId', $shop['id'])
            ->setParameter('parentShopId', !empty($shop['main_id']) ? $shop['main_id'] : 1)
        ;

        $data = $builder->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);

        return $this->hydrate($data);
    }

    public function hydrate(array $config): array
    {
        $config = array_map('unserialize', $config);
        $config['version'] = Framework::VERSION;
        $config['revision'] = Framework::REVISION;
        $config['versiontext'] = Framework::VERSION_TEXT;

        return $config;
    }
}
