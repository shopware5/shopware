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

namespace Shopware\Recovery\Install;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Shopware\Recovery\Common\DumpIterator;
use Shopware\Recovery\Common\HttpClient\CurlClient;
use Shopware\Recovery\Common\Service\Notification;
use Shopware\Recovery\Common\Service\UniqueIdGenerator;
use Shopware\Recovery\Common\Service\UniqueIdPersister;
use Shopware\Recovery\Common\SystemLocker;
use Shopware\Recovery\Install\Service\ConfigWriter;
use Shopware\Recovery\Install\Service\DatabaseService;
use Shopware\Recovery\Install\Service\LicenseInstaller;
use Shopware\Recovery\Install\Service\LocalLicenseUnpackService;
use Shopware\Recovery\Install\Service\ThemeService;
use Shopware\Recovery\Install\Service\TranslationService;
use Shopware\Recovery\Install\Service\WebserverCheck;

class ContainerProvider implements ServiceProviderInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['config'] = $this->config;

        $container['shopware.version'] = function () {
            $version = trim(file_get_contents(__DIR__ . '/../data/version'));

            return $version;
        };

        $container['slim.app'] = function ($c) {
            $slimOptions = $c['config']['slim'];
            $slim = new \Slim\Slim($slimOptions);
            $slim->contentType('text/html; charset=utf-8');

            $c['slim.request'] = $slim->request();
            $c['slim.response'] = $slim->response();

            return $slim;
        };

        $container['system.locker'] = function ($c) {
            return new SystemLocker(
                SW_PATH . '/recovery/install/data/install.lock'
            );
        };

        $container['translation.service'] = function ($c) {
            return new TranslationService($c['translations']);
        };

        // dump class contains state so we define it as factory here
        $container['database.dump_iterator'] = $container->factory(function ($c) {
            $dumpFile = __DIR__ . '/../data/sql/install.sql';

            return new DumpIterator($dumpFile);
        });

        // dump class contains state so we define it as factory here
        $container['database.dump_iterator_en_gb'] = $container->factory(function ($c) {
            $dumpFile = __DIR__ . '/../data/sql/en.sql';

            return new DumpIterator($dumpFile);
        });

        // dump class contains state so we define it as factory here
        $container['database.snippet_dump_iterator'] = $container->factory(function ($c) {
            $dumpFile = __DIR__ . '/../data/sql/snippets.sql';

            return new DumpIterator($dumpFile);
        });

        $container['shopware.container'] = function (Container $c) {
            require_once SW_PATH . '/autoload.php';

            $kernel = new \Shopware\Kernel('production', false);
            $kernel->boot();

            $container = $kernel->getContainer();
            $container->get('models')->generateAttributeModels();

            return $container;
        };

        $container['shopware.theme_installer'] = function ($c) {
            $shopwareContainer = $c['shopware.container'];

            /* @var \Shopware\Components\Theme\Installer $themeInstaller */
            return $shopwareContainer->get('theme_installer');
        };

        $container['http-client'] = function ($c) {
            return new CurlClient();
        };

        $container['theme.service'] = function ($c) {
            return new ThemeService(
                $c['db'],
                $c['shopware.theme_installer']
            );
        };

        $container['install.requirements'] = function ($c) {
            return new Requirements(SW_PATH . '/engine/Shopware/Components/Check/Data/System.xml', $c['translation.service']);
        };

        $container['install.requirementsPath'] = function ($c) {
            $check = new RequirementsPath(SW_PATH, SW_PATH . '/engine/Shopware/Components/Check/Data/Path.xml');
            $check->addFile('recovery/install/data');

            return $check;
        };

        $container['db'] = function ($c) {
            throw new \RuntimeException('Identifier DB not initialized yet');
        };

        $container['config.writer'] = function ($c) {
            return new ConfigWriter(SW_PATH . '/config.php');
        };

        $container['webserver.check'] = function ($c) {
            return new WebserverCheck(
                $c['config']['check.ping_url'],
                $c['config']['check.check_url'],
                $c['config']['check.token.path'],
                $c['http-client']
            );
        };

        $container['database.service'] = function ($c) {
            return new DatabaseService($c['db']);
        };

        $container['license.service'] = function ($c) {
            return new LocalLicenseUnpackService();
        };

        $container['license.installer'] = function ($c) {
            return new LicenseInstaller($c['db']);
        };

        $container['menu.helper'] = function ($c) {
            $routes = $c['config']['menu.helper']['routes'];

            return new MenuHelper(
                $c['slim.app'],
                $c['translation.service'],
                $routes
            );
        };

        $container['uniqueid.generator'] = function ($c) {
            return new UniqueIdGenerator(
                SW_PATH . '/recovery/install/data/uniqueid.txt'
            );
        };

        $container['uniqueid.persister'] = function ($c) {
            return new UniqueIdPersister(
                $c['uniqueid.generator'],
                $c['db']
            );
        };

        $container['shopware.notify'] = function ($c) {
            return new Notification(
                $c['config']['api.endpoint'],
                $c['uniqueid.generator']->getUniqueId(),
                $c['http-client']
            );
        };
    }
}
