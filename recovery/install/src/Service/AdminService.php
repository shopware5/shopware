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

use Shopware\Recovery\Install\Struct\AdminUser;

/**
 * @category  Shopware
 * @package   Shopware\Recovery\Install\Service
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class AdminService
{
    /**
     * @var \PDO
     */
    private $connection;

    /**
     * @param \PDO $connection
     */
    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param  AdminUser         $user
     * @throws \RuntimeException
     */
    public function createAdmin(AdminUser $user)
    {
        $localeId = $this->getLocaleId($user);

        // Drop previous inserted admins
        $this->connection->query("DELETE FROM s_core_auth");

        $sql = <<<EOT
INSERT INTO s_core_auth
(roleID,username,password,localeID,`name`,email,active,lockeduntil)
VALUES
(1,?,?,?,?,?,1,'0000-00-00 00:00:00');
EOT;

        $prepareStatement = $this->connection->prepare($sql);
        $prepareStatement->execute([
            $user->username,
            $this->saltPassword($user->password),
            $localeId,
            $user->name,
            $user->email
        ]);
    }

    /**
     * @param AdminUser $user
     * @return int
     */
    private function getLocaleId(AdminUser $user)
    {
        $localeId = $this->connection->prepare("SELECT id FROM s_core_locales WHERE locale = ?");
        $localeId->execute([$user->locale]);
        $localeId = $localeId->fetchColumn();

        if (!$localeId) {
            throw new \RuntimeException("Could not resolve language " . $user->locale);
        }

        return (int)$localeId;
    }

    /**
     * @param string $password
     *
     * @return string
     */
    private function saltPassword($password)
    {
        return md5("A9ASD:_AD!_=%a8nx0asssblPlasS$" . md5($password));
    }

    public function addWidgets(AdminUser $adminUser)
    {
        $query = $this->connection->prepare('SELECT id FROM s_core_auth WHERE username = ? LIMIT 1');
        $query->execute([$adminUser->username]);
        $userId = $query->fetchColumn();

        $query = $this->connection->prepare('SELECT id FROM s_core_widgets WHERE NAME = ? LIMIT 1');
        $query->execute(['swag-shopware-news-widget']);
        $widgetId = $query->fetchColumn();

        $insert = $this->connection->prepare('INSERT INTO s_core_widget_views (widget_id, auth_id) VALUES (?, ?)');
        $insert->execute([$widgetId, $userId]);
    }
}
