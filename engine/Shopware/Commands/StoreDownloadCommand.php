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

namespace Shopware\Commands;

use Shopware\Bundle\PluginInstallerBundle\Context\DownloadRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\PluginsByTechnicalNameRequest;
use Shopware\Bundle\PluginInstallerBundle\Service\PluginLicenceService;
use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\PluginStruct;
use Shopware\Models\Plugin\Plugin;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class StoreDownloadCommand extends StoreCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:store:download')
            ->setDescription('Downloads a plugin from the community store')
            ->addArgument(
                'technical-name',
                InputArgument::REQUIRED,
                'Name of the plugin to be downloaded.'
            )
            ->addOption(
                'username',
                null,
                InputOption::VALUE_OPTIONAL
            )
            ->addOption(
                'password',
                null,
                InputOption::VALUE_OPTIONAL
            )
            ->addOption(
                'shopware-version',
                null,
                InputOption::VALUE_OPTIONAL,
                'Override shopware version eg. 4.2.0'
            )
            ->addOption(
                'domain',
                null,
                InputOption::VALUE_OPTIONAL,
                'Override default shop domain.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $technicalName = $input->getArgument('technical-name');
        $domain = $this->checkDomain();
        $version = $this->checkVersion();

        $service = $this->container->get('shopware_plugininstaller.plugin_service_view');
        $context = new PluginsByTechnicalNameRequest(null, $version, [$technicalName]);
        $plugin = $service->getPlugin($context);

        if (!$plugin) {
            $output->writeln(sprintf('Plugin %s not found', $technicalName));

            return;
        }

        $output->writeln(sprintf('Checking system requirements for plugin %s', $plugin->getLabel()));

        $this->checkLicenceManager($plugin);
        $this->checkIonCubeLoader($plugin);

        $isDummy = ($plugin->hasCapabilityDummy() || $plugin->getTechnicalName() == 'SwagLicense');

        switch (true) {
            case $plugin->getId() && $isDummy:
                $this->handleDummyUpdate($plugin, $domain, $version);
                break;

            case $isDummy:
                $this->handleDummyInstall($plugin, $domain, $version);
                break;

            case $plugin->getId():
                $this->handleLicenceUpdate($plugin, $domain, $version);
                break;

            default:
                $this->handleLicenceInstall($plugin, $domain, $version);
                break;
        }

        try {
            $this->clearOpcodeCache();
            $this->container->get('shopware_plugininstaller.plugin_manager')->refreshPluginList();
        } catch (\Exception $e) {
        }
    }

    /**
     * @param PluginStruct $plugin
     * @param string       $domain
     * @param string       $version
     *
     * @throws \Exception
     */
    private function handleDummyUpdate(PluginStruct $plugin, $domain, $version)
    {
        if (!$plugin->isUpdateAvailable()) {
            $this->output->writeln(sprintf('No update available for plugin %s', $plugin->getLabel()));

            return;
        }

        $this->output->writeln(sprintf('Download plugin update package %s', $plugin->getLabel()));

        $request = new DownloadRequest($plugin->getTechnicalName(), $version, $domain, null);

        $model = $this->getPluginModel($plugin->getTechnicalName());
        if ($plugin->isActive()) {
            $this->container->get('shopware_plugininstaller.plugin_manager')->deactivatePlugin($model);
        }

        $this->container->get('shopware_plugininstaller.plugin_download_service')
            ->download($request);
    }

    /**
     * @param PluginStruct $plugin
     * @param string       $version
     * @param $domain
     *
     * @throws \Exception
     */
    private function handleDummyInstall(PluginStruct $plugin, $domain, $version)
    {
        $this->output->writeln(sprintf('Download plugin install package %s', $plugin->getLabel()));

        $request = new DownloadRequest($plugin->getTechnicalName(), $version, $domain, null);

        $this->container->get('shopware_plugininstaller.plugin_download_service')->download($request);
    }

    /**
     * @param PluginStruct $plugin
     * @param string       $domain
     * @param string       $version
     *
     * @throws \Exception
     */
    private function handleLicenceUpdate(PluginStruct $plugin, $domain, $version)
    {
        if (!$plugin->isUpdateAvailable()) {
            $this->output->writeln(sprintf('No update available for plugin %s', $plugin->getLabel()));

            return;
        }
        $token = $this->checkAuthentication();

        $this->output->writeln(sprintf('Download plugin update package', $plugin->getLabel()));

        $model = $this->getPluginModel($plugin->getTechnicalName());
        if ($plugin->isActive()) {
            $this->container->get('shopware_plugininstaller.plugin_manager')
                ->deactivatePlugin($model);
        }

        $request = new DownloadRequest($plugin->getTechnicalName(), $version, $domain, $token);
        $this->container->get('shopware_plugininstaller.plugin_download_service')
            ->download($request);
    }

    /**
     * @param PluginStruct $plugin
     * @param string       $domain
     * @param string       $version
     *
     * @throws \Exception
     */
    private function handleLicenceInstall(PluginStruct $plugin, $domain, $version)
    {
        $token = $this->checkAuthentication();

        $this->output->writeln(sprintf('Download plugin install package', $plugin->getLabel()));

        $request = new DownloadRequest($plugin->getTechnicalName(), $version, $domain, $token);

        /* @var $service PluginLicenceService */
        $this->container->get('shopware_plugininstaller.plugin_download_service')->download($request);
    }

    /**
     * @return bool
     */
    private function isIonCubeLoaderLoaded()
    {
        return extension_loaded('ionCube Loader');
    }

    private function getPluginModel($technicalName)
    {
        $repo = $this->container->get('models')->getRepository('Shopware\Models\Plugin\Plugin');
        $plugin = $repo->findOneBy(['name' => $technicalName]);

        return $plugin;
    }

    private function checkDomain()
    {
        $domain = $this->input->getOption('domain');
        if (empty($domain)) {
            $domain = $this->container->get('shopware_plugininstaller.account_manager_service')->getDomain();
        }

        return $domain;
    }

    private function checkVersion()
    {
        $version = $this->input->getOption('shopware-version');
        if (empty($version)) {
            $version = \Shopware::VERSION;
        }

        return $version;
    }

    /**
     * @param PluginStruct $struct
     *
     * @throws \Exception
     */
    private function checkLicenceManager(PluginStruct $struct)
    {
        if (!$struct->hasLicenceCheck()) {
            return;
        }

        $repo = $this->container->get('models')->getRepository('Shopware\Models\Plugin\Plugin');
        $plugin = $repo->findOneBy(['name' => 'SwagLicense']);

        switch (true) {
            case !$plugin instanceof Plugin:
                $this->handleError(['message' => sprintf("Plugin %s contains a licence check and the licence manager doesn't exist in your system.", $struct->getLabel())]);
                break;
            case $plugin->getInstalled() == null:
                $this->handleError(['message' => sprintf('Plugin %s contains a licence check and the licence manager is not installed', $struct->getLabel())]);
                break;
            case !$plugin->getActive():
                $this->handleError(['message' => sprintf("Plugin %s contains a licence check and the licence manager isn't activated", $struct->getLabel())]);
                break;
        }
    }

    /**
     * @param PluginStruct $plugin
     *
     * @throws \Exception
     */
    private function checkIonCubeLoader(PluginStruct $plugin)
    {
        if (!$plugin->isEncrypted()) {
            return;
        }

        if ($this->isIonCubeLoaderLoaded()) {
            return;
        }

        $this->handleError([
            'message' => sprintf('Plugin %s is encrypted and requires the ioncube loader extension', $plugin->getLabel()),
        ]);
    }

    /**
     * @throws \Exception
     *
     * @return AccessTokenStruct
     */
    private function checkAuthentication()
    {
        $username = $this->input->getOption('username');
        $password = $this->input->getOption('password');

        if ($this->input->isInteractive()) {
            $questionHelper = $this->getHelper('question');

            if (empty($username)) {
                $username = $questionHelper->ask($this->input, $this->output, new Question('Please enter the username: '));
            }

            if (empty($password)) {
                $question = new Question('Please enter the password: ');
                $question->setHidden(true);
                $question->setHiddenFallback(false);

                $password = $questionHelper->ask($this->input, $this->output, $question);
            }
        }

        if (empty($username) || empty($password)) {
            throw new \Exception('Username and password are required');
        }

        $this->output->writeln(sprintf('Connect to Store with username: %s...', $username));

        return $this->container->get('shopware_plugininstaller.store_client')->getAccessToken(
            $username,
            $password
        );
    }

    /**
     * Clear opcode caches to make sure that the
     * updated plugin files are used in the following requests.
     */
    private function clearOpcodeCache()
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (function_exists('apcu_clear_cache')) {
            apcu_clear_cache();
        }
    }
}
