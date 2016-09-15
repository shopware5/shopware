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

use Shopware\Bundle\PluginInstallerBundle\Service\InstallerServiceInterface;
use Shopware\Components\Model\ModelManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 * @package   Shopware\Components\Console\Command
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class PluginConfigSetCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:plugin:config:set')
            ->setDescription('Sets plugin configuration.')
            ->addArgument(
                'plugin',
                InputArgument::REQUIRED,
                'Name of the plugin.'
            )
            ->addArgument(
                'key',
                InputArgument::REQUIRED,
                'Configuration key.'
            )
            ->addArgument(
                'value',
                InputArgument::REQUIRED,
                'Configuration value. Can be true, false, null, an integer or an array specified with brackets: [value,anothervalue]. Everything else will be interpreted as string.'
            )
            ->addOption(
                'shop',
                null,
                InputOption::VALUE_OPTIONAL,
                'Set configuration for shop id'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var InstallerServiceInterface $pluginManager */
        $pluginManager  = $this->container->get('shopware_plugininstaller.plugin_manager');
        $pluginName = $input->getArgument('plugin');

        try {
            $plugin = $pluginManager->getPluginByName($pluginName);
        } catch (\Exception $e) {
            $output->writeln(sprintf('Plugin by name "%s" was not found.', $pluginName));
            return 1;
        }

        /**@var ModelManager $em */
        $em = $this->container->get('models');

        if ($input->getOption('shop')) {
            $shop = $em->getRepository('Shopware\Models\Shop\Shop')->find($input->getOption('shop'));
            if (!$shop) {
                $output->writeln(sprintf('Could not find shop with id %s.', $input->getOption('shop')));
                return 1;
            }
        } else {
            $shop = $em->getRepository('Shopware\Models\Shop\Shop')->findOneBy(array('default' => true));
        }

        $rawValue = $input->getArgument('value');
        $value = $this->castValue($rawValue);

        if (preg_match('/^\[(.+,?)*\]$/', $value, $matches) && count($matches) == 2) {
            $value = explode(',', $matches[1]);
            $value = array_map(function ($val) {
                return $this->castValue($val);
            }, $value);
        }

        $pluginManager->saveConfigElement($plugin, $input->getArgument('key'), $value, $shop);
        $output->writeln(sprintf("Plugin configuration for Plugin %s saved.", $pluginName));
    }

    /**
     * Casts a given string into the proper type.
     * Works only for some types, see return.
     *
     * @param $value
     * @return bool|int|null|string
     */
    private function castValue($value)
    {
        if ($value === "null") {
            return null;
        }
        if ($value === "false") {
            return false;
        }
        if ($value === "true") {
            return true;
        }
        if (preg_match('/^\d+$/', $value)) {
            return intval($value);
        }
        return $value;
    }
}
