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

namespace Shopware\Bundle\PluginInstallerBundle\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\PluginInstallerBundle\Context\BaseRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\ListingRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\PluginsByTechnicalNameRequest;
use Shopware\Bundle\PluginInstallerBundle\Struct\ListingResultStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\PluginStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\StructHydrator;

class PluginLocalService
{
    private Connection $connection;

    private StructHydrator $hydrator;

    private string $shopwareRootDir;

    private InstallerService $installerService;

    private \Enlight_Controller_Front $front;

    public function __construct(
        Connection $connection,
        StructHydrator $hydrator,
        string $shopwareRootDir,
        InstallerService $installerService,
        \Enlight_Controller_Front $front
    ) {
        $this->connection = $connection;
        $this->hydrator = $hydrator;
        $this->shopwareRootDir = $shopwareRootDir;
        $this->installerService = $installerService;
        $this->front = $front;
    }

    /**
     * @throws \Exception
     *
     * @return ListingResultStruct
     */
    public function getListing(ListingRequest $context)
    {
        $query = $this->getQuery()
            ->andWhere("plugin.name != 'PluginManager'")
            ->andWhere('plugin.capability_enable = 1');

        $this->addSortings($context, $query);

        $data = $query->setFirstResult($context->getOffset())
            ->setMaxResults($context->getLimit())
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        $plugins = $this->iteratePlugins($data, $context);

        return new ListingResultStruct($plugins, \count($plugins));
    }

    /**
     * @return PluginStruct
     */
    public function getPlugin(PluginsByTechnicalNameRequest $context)
    {
        $plugins = $this->getPlugins($context);
        if ($plugins === []) {
            throw new \RuntimeException(sprintf('Plugin "%s" not found', implode(',', $context->getTechnicalNames())));
        }

        return array_shift($plugins);
    }

    /**
     * @throws \Exception
     *
     * @return PluginStruct[]
     */
    public function getPlugins(PluginsByTechnicalNameRequest $context)
    {
        $query = $this->getQuery();
        $query->andWhere('plugin.name IN (:names)')
            ->setParameter(
                ':names',
                $context->getTechnicalNames(),
                Connection::PARAM_STR_ARRAY
            );

        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $this->iteratePlugins($data, $context);
    }

    /**
     * @return array<string, string> indexed by technical name, value contains the version
     */
    public function getPluginsForUpdateCheck()
    {
        return $this->connection->createQueryBuilder()
            ->select(['plugin.name', 'plugin.version'])
            ->from('s_core_plugins', 'plugin')
            ->where('plugin.capability_update = 1')
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    private function addSortings(ListingRequest $context, QueryBuilder $builder): void
    {
        foreach ($context->getSortings() as $sort) {
            if (!isset($sort['property'])) {
                continue;
            }
            $sortDirection = $sort['direction'] ?? 'ASC';

            $builder->addOrderBy($sort['property'], $sortDirection);
        }
    }

    /**
     * @param array<string, mixed> $plugins
     *
     * @return PluginStruct[]
     */
    private function iteratePlugins(array $plugins, BaseRequest $context): array
    {
        $locale = substr($context->getLocale(), 0, 2);

        foreach ($plugins as &$row) {
            try {
                $row['iconPath'] = $this->getIconOfPlugin($row['name']);
            } catch (\InvalidArgumentException $e) {
                $row['iconPath'] = null;
            }

            $translations = json_decode($row['translations'], true);

            if (isset($translations[$locale]['label'])) {
                $row['label'] = $translations[$locale]['label'];
            }

            if (isset($translations[$locale]['description'])) {
                $row['description'] = $translations[$locale]['description'];
            }

            if (!empty($row['changes'])) {
                $row['changes'] = json_decode($row['changes'], true);
                $changelog = [];

                foreach ($row['changes'] as $version => $item) {
                    $lang = isset($item[$locale]) ? $locale : 'en';

                    if (isset($item[$lang])) {
                        $changelog[] = [
                            'version' => $version,
                            // The implode concatenates multiple entries for one language
                            'text' => $this->parseChangeLog(trim(implode('', $item[$lang]))),
                        ];
                    }
                }

                $row['changelog'] = $changelog;
            }
        }

        return $this->hydrator->hydrateLocalPlugins($plugins);
    }

    private function getIconOfPlugin(string $name): ?string
    {
        $path = $this->installerService->getPluginPath($name);
        $path .= '/plugin.png';

        $relativePath = str_replace($this->shopwareRootDir, '', $path);

        if (file_exists($path) && $this->front->Request() !== null) {
            return $this->front->Request()->getBasePath() . '/' . ltrim($relativePath, '/');
        }

        return null;
    }

    private function getQuery(): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $query->select([
            'plugin.id',
            'plugin.name',
            'plugin.label',
            'plugin.active',
            'plugin.namespace',
            'plugin.description',
            'plugin.source',
            'plugin.version as version',
            'plugin.capability_update',
            'plugin.capability_install',
            'plugin.capability_enable',
            'plugin.capability_secure_uninstall',
            'plugin.update_version',
            'plugin.translations',
            'plugin.in_safe_mode',

            'plugin.installation_date',
            'forms.id as form_id',
            'plugin.update_date',
            'plugin.author',
            'plugin.link',
            'plugin.support',
            'plugin.changes',

            'licence.id as __licence_id',
            'licence.host as __licence_host',
            'licence.type as __licence_type',
            'licence.creation as __licence_creation',
            'licence.expiration as __licence_expiration',
            'licence.license as __licence_license',
        ]);

        $query->from('s_core_plugins', 'plugin')
            ->leftJoin('plugin', 's_core_config_forms', 'forms', 'forms.plugin_id = plugin.id')
            ->leftJoin('plugin', 's_core_licenses', 'licence', 'licence.module = plugin.name')
            ->groupBy('plugin.id');

        return $query;
    }

    /**
     * Removes all but allowed tags and attributes from the content of the HTML.
     */
    private function parseChangeLog(string $html): string
    {
        $html = strip_tags($html, '<br><i><b><strong><em><del><u><div><span><ul><li><ll><ol><p><a>');

        if ($html === '') {
            return '';
        }

        $dom = new \DOMDocument();
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

        $nodes = (new \DOMXPath($dom))->query("//@*[local-name() != 'href']");
        if ($nodes === false) {
            return '';
        }

        foreach ($nodes as $node) {
            $parentNode = $node->parentNode;
            if ($parentNode instanceof \DOMElement) {
                $parentNode->removeAttribute($node->nodeName);
            }
        }

        $changelog = $dom->saveHTML();
        if ($changelog === false) {
            return '';
        }

        return $changelog;
    }
}
