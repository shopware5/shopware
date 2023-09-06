<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Commands;

use Exception;
use RuntimeException;
use Shopware\Components\Theme\Configuration;
use Shopware\Models\Shop\Shop;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ThemeDumpConfigurationCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:theme:dump:configuration')
            ->setDescription('Dumps the theme configuration into json files')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repository = $this->container->get(\Shopware\Components\Model\ModelManager::class)->getRepository(Shop::class);
        $shops = $repository->getShopsWithThemes()->getResult();
        $compiler = $this->container->get('theme_compiler');
        $rootDir = $this->container->getParameter('shopware.app.rootDir');

        if (!\is_string($rootDir)) {
            throw new RuntimeException('Parameter shopware.app.rootDir has to be an string');
        }

        /** @var Shop $shop */
        foreach ($shops as $shop) {
            $configuration = $compiler->getThemeConfiguration($shop);
            $file = $this->dumpConfiguration($shop, $configuration);
            $file = str_replace($rootDir, '', $file);
            $output->writeln('file: ' . $file . ' generated');
        }

        return 0;
    }

    /**
     * @throws Exception
     *
     * @return string
     */
    private function dumpConfiguration(Shop $shop, Configuration $configuration)
    {
        $pathResolver = $this->container->get(\Shopware\Components\Theme\PathResolver::class);
        $file = $pathResolver->getCacheDirectory() . '/config_' . $shop->getId() . '.json';

        file_put_contents($file, json_encode($configuration, JSON_PRETTY_PRINT));

        return $file;
    }
}
