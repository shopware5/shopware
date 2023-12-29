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

use Doctrine\DBAL\Connection;
use RuntimeException;
use Shopware\Components\Theme\Installer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ThemeInitializeCommand extends ShopwareCommand
{
    /**
     * @var Connection
     */
    private $conn;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:theme:initialize')
            ->setDescription('Initializes themes. Enables responsive theme for the default shop.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $themeInstaller = $this->container->get(Installer::class);
        $themeInstaller->synchronize();

        $this->conn = $this->container->get(Connection::class);

        $templateId = $this->getResponsiveTemplateId();
        $this->updateDefaultTemplateId($templateId);

        $output->writeln('Themes initialized');

        return 0;
    }

    /**
     * @return int
     */
    private function getResponsiveTemplateId()
    {
        $statement = $this->conn->query('SELECT id FROM s_core_templates WHERE template LIKE "Responsive"');
        $statement->execute();
        $templateId = $statement->fetchColumn();

        if (!$templateId) {
            throw new RuntimeException('Could not get id for default template');
        }

        return (int) $templateId;
    }

    /**
     * @param int $templateId
     */
    private function updateDefaultTemplateId($templateId)
    {
        $sql = <<<'EOF'
UPDATE s_core_shops
SET template_id = :templateId,
    document_template_id = :templateId
WHERE `default` = 1
EOF;

        $statement = $this->conn->prepare($sql);
        $statement->execute(['templateId' => $templateId]);
    }
}
