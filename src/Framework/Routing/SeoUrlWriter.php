<?php

namespace Shopware\Framework\Routing;

use Doctrine\DBAL\Connection;
use Shopware\Context\TranslationContext;

class SeoUrlWriter
{
    const LIMIT = 100;

    /**
     * @var SeoUrlGeneratorInterface[]
     */
    private $generators;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection, array $generators)
    {
        $this->generators = $generators;
        $this->connection = $connection;
    }

    public function write(int $shopId, TranslationContext $context): void
    {
        foreach ($this->generators as $generator) {

            $this->connection->transactional(
                function () use ($shopId, $generator, $context) {

                    $this->connection->executeUpdate(
                        "DELETE FROM seo_route WHERE shop_id = :shopId AND name = :name",
                        [':shopId' => $shopId, ':name' => $generator->getName()]
                    );

                    $offset = 0;

                    while ($routes = $generator->fetch($shopId, $context, $offset, self::LIMIT)) {
                        $this->writeUrls($shopId, $generator->getName(), $routes);
                        $offset += self::LIMIT;
                    }
                }
            );
        }
    }

    /**
     * @param int $shopId
     * @param string $name
     * @param SeoRoute[] $routes
     */
    private function writeUrls(int $shopId, string $name, array $routes): void
    {
        foreach ($routes as $route) {
            $this->connection->insert('seo_route', [
                'shop_id' => $shopId,
                'url' => $route->getUrl(),
                'seo_url' => $route->getSeoUrl(),
                'name' => $name
            ]);
        }
    }
}