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

use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FirstRunWizardDisableCommand extends ShopwareCommand
{
    protected function configure(): void
    {
        $this
            ->setName('sw:firstrunwizard:disable')
            ->setDescription('Disable the first run wizard.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conn = $this->container->get(\Doctrine\DBAL\Connection::class);
        $elementId = $conn->fetchColumn('SELECT id FROM s_core_config_elements WHERE name LIKE "firstRunWizardEnabled"');
        $elementId = is_numeric($elementId) ? (int) $elementId : 0;
        if ($elementId <= 0) {
            throw new RuntimeException('Cannot find config element `firstRunWizardEnabled`');
        }

        $valueid = $conn->fetchColumn('SELECT id FROM s_core_config_values WHERE element_id = :elementId', ['elementId' => $elementId]);
        $valueid = is_numeric($valueid) ? (int) $valueid : 0;

        $data = [
            'element_id' => $elementId,
            'shop_id' => 1,
            'value' => serialize(false),
        ];

        if ($valueid > 0) {
            $conn->update(
                's_core_config_values',
                $data,
                ['id' => $valueid]
            );
        } else {
            $conn->insert('s_core_config_values', $data);
        }

        /** @var \Shopware\Components\CacheManager $cacheManager */
        $cacheManager = $this->container->get(\Shopware\Components\CacheManager::class);
        $cacheManager->clearConfigCache();

        $output->writeln('<info>First Run Wizard disabled</info>');

        return 0;
    }
}
