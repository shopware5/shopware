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

namespace Shopware\Recovery\Install\Service;

use Shopware\Components\Theme\Installer as ThemeInstaller;

class ThemeService
{
    /**
     * @var \PDO
     */
    private $conn;

    /**
     * @var ThemeInstaller
     */
    private $themeInstaller;

    public function __construct(\PDO $conn, ThemeInstaller $themeInstaller)
    {
        $this->themeInstaller = $themeInstaller;
        $this->conn = $conn;
    }

    public function activateResponsiveTheme()
    {
        $this->themeInstaller->synchronize();

        $templateId = $this->getResponsiveTemplateId();

        $this->updateDefaultTemplateId($templateId);
    }

    /**
     * @return int
     */
    private function getResponsiveTemplateId()
    {
        $statement = $this->conn->query('SELECT id FROM s_core_templates WHERE template LIKE "Responsive"');
        $statement->execute();
        $templateId = $statement->fetchColumn(0);

        if (!$templateId) {
            throw new \RuntimeException('Could not get id for default template');
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
