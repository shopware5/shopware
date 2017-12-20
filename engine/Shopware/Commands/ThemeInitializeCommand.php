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

use Shopware\Components\Theme\Installer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ThemeInitializeCommand extends ShopwareCommand
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $conn;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:theme:initialize')
            ->setDescription('Initializes themes. Enables responsive theme for the default shop.')
            ->addArgument('template',InputArgument::OPTIONAL,'Name of the template to initialize.','Responsive');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Installer $themeInstaller */
        $themeInstaller = $this->container->get('theme_installer');
        $themeInstaller->synchronize();

        $this->conn = $this->container->get('dbal_connection');

        $templateName = $input->getArgument('template');
        $templateId = $this->getResponsiveTemplateId($templateName);
        $this->updateDefaultTemplateId($templateId);

        $output->writeln(sprintf('Theme %s initialized', $templateName));
    }

    /**
     * @param string $templateName
     *
     * @return int
     */
    private function getResponsiveTemplateId($templateName)
    {
        $statement = $this->conn->query(sprintf('SELECT id FROM s_core_templates WHERE template LIKE %s', $this->conn->quote($templateName)));
        $statement->execute();
        $templateId = $statement->fetchColumn(0);

        if (!$templateId) {
            throw new \RuntimeException(sprintf('Could not get id for template %s', $templateName));
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
