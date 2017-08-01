<?php
declare(strict_types=1);

namespace Shopware\Framework\Translator;

use Doctrine\DBAL\Connection;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

class DatabaseLoader implements LoaderInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Loads a locale.
     *
     * @param mixed $resource A resource
     * @param string $locale  A locale
     * @param string $domain  The domain
     *
     * @return MessageCatalogue A MessageCatalogue instance
     *
     * @throws NotFoundResourceException when the resource cannot be found
     * @throws InvalidResourceException  when the resource cannot be loaded
     */
    public function load($resource, $locale, $domain = 'messages'): MessageCatalogue
    {
        $builder = $this->connection->createQueryBuilder();

        $snippets = $builder->select(['snippet.namespace', 'snippet.name', 'snippet.value'])
                ->from('s_core_snippets', 'snippet')
                ->innerJoin('snippet', 's_core_locales', 'locale', 'snippet.localeID = locale.id')
                ->where('locale.locale = :locale')
                ->setParameter('locale', $locale)
                ->execute()
                ->fetchAll(\PDO::FETCH_GROUP);

        $catalogue = new MessageCatalogue($locale);

        foreach ($snippets as $namespace => $snippet) {
            foreach ($snippet as $item) {
                $catalogue->set($item['name'], $item['value'], $namespace);
            }
        }

        return $catalogue;
    }
}
