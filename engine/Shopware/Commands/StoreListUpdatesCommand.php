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

use Shopware\Bundle\PluginInstallerBundle\Context\UpdateListingRequest;
use Shopware\Bundle\PluginInstallerBundle\Struct\UpdateResultStruct;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StoreListUpdatesCommand extends StoreCommand implements CompletionAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        if ($optionName === 'shopware-version') {
            return $this->completeShopwareVersions($context->getCurrentWord());
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function completeArgumentValues($argumentName, CompletionContext $context)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::addConfigureShopwareVersion();

        $this
            ->setName('sw:store:list:updates')
            ->setDescription('Lists updates for installed plugins.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version = $input->getOption('shopware-version');
        if (empty($version)) {
            $version = $this->container->getParameter('shopware.release.version');
        }

        $plugins = $this->container->get('shopware_plugininstaller.plugin_service_local')->getPluginsForUpdateCheck();
        $domain = $this->container->get('shopware_plugininstaller.account_manager_service')->getDomain();
        $service = $this->container->get('shopware_plugininstaller.plugin_service_view');
        $request = new UpdateListingRequest('', $version, $domain, $plugins);
        /** @var UpdateResultStruct $updates */
        $updates = $service->getUpdates($request);
        $plugins = $updates->getPlugins();

        $result = [];
        foreach ($plugins as $plugin) {
            $result[] = [
                $plugin->getId(),
                $plugin->getTechnicalName(),
                $plugin->getLabel(),
                $plugin->getVersion(),
                $plugin->getAvailableVersion(),
            ];
        }

        $table = new Table($output);
        $table->setHeaders(['Id', 'Technical name', 'Label',  'CurrentVersion', 'AvailableVersion'])
              ->setRows($result);

        $table->render();
    }
}
