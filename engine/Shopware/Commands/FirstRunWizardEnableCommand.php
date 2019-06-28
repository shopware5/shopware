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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FirstRunWizardEnableCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:firstrunwizard:enable')
            ->setDescription('Enable the first run wizard.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conn = $this->container->get('dbal_connection');
        $elementId = $conn->fetchColumn('SELECT id FROM s_core_config_elements WHERE name LIKE "firstRunWizardEnabled"');
        $valueid = $conn->fetchColumn('SELECT id FROM s_core_config_values WHERE element_id = :elementId', ['elementId' => $elementId]);

        $data = [
            'element_id' => $elementId,
            'shop_id' => 1,
            'value' => serialize(true),
        ];

        if ($valueid) {
            $conn->update(
                's_core_config_values',
                $data,
                ['id' => $valueid]
            );
        } else {
            $conn->insert('s_core_config_values', $data);
        }

        /** @var \Shopware\Components\CacheManager $cacheManager */
        $cacheManager = $this->container->get('shopware.cache_manager');
        $cacheManager->clearConfigCache();

        $output->writeln('<info>First Run Wizard enabled</info>');
    }
}
