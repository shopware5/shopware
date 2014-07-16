<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Recovery\Install;

/**
 * @category  Shopware
 * @package   Shopware\Recovery\Update
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Configuration
{
    /**
     * @var \PDO
     */
    protected $database;

    /**
     * @var array
     */
    protected $requiredAdminParameters = array('');

    /**
     * @var
     */
    protected $error;

    /**
     * @param $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * @return \PDO
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return bool
     */
    public function getCurrencies()
    {
        $db = $this->getDatabase();
        try {
            $fetchAllCurrencies = $db->query(
                "
                            SELECT * FROM s_core_currencies
                            "
            );
            $fetchAllCurrencies = $fetchAllCurrencies->fetchAll();

            return $fetchAllCurrencies;
        } catch (\PDOException $e) {
            $this->setError($e->getMessage());

            return false;
        }
    }

    /**
     * @param $params
     *
     * @return bool
     */
    public function updateShop($params)
    {
        if (empty($params["c_config_shop_language"]) || empty($params["c_config_shop_currency"])
        ) {
            $this->setError("Please fill in all required fields. (shop configuration)");

            return false;
        }
        try {
            $host = $this->getShopDomain();
            $basepath = $host["basepath"];
            $host = $host["domain"];

            $fetchLanguageId = $this->getDatabase()->prepare(
                "
                            SELECT id FROM s_core_locales WHERE locale = ?
                            "
            );
            $fetchLanguageId->execute(array($params["c_config_shop_language"]));
            $fetchLanguageId = $fetchLanguageId->fetchColumn();
            if (!$fetchLanguageId) {
                throw new \Exception ("Language with id " . $params["c_config_shop_language"] . " not found");
            }

            // Do update on s_core_shops
            if ($params["c_config_shop_language"] == "de_DE") {
                $name = "Hauptshop Deutsch";
            } else {
                $name = "Default english";
            }

            // Update s_core_shops
            $sql = "
            UPDATE s_core_shops SET `name` = ?, locale_id =  ?, currency_id = ?, host = ?, base_path = ?,hosts = ? WHERE `default` = 1
            ";
            $prepareStatement = $this->getDatabase()->prepare($sql);
            $prepareStatement->execute(
                array(
                    $name,
                    $fetchLanguageId,
                    $params["c_config_shop_currency"],
                    $host,
                    $basepath,
                    $host
                )
            );

            // Update s_core_multilanguage
            $sql = "
            UPDATE s_core_multilanguage SET `name` = ?, defaultcurrency =  ?, locale = ?, domainaliase = ? WHERE `default` = 1
            ";
            $prepareStatement = $this->getDatabase()->prepare($sql);
            $prepareStatement->execute(
                array(
                    $name,
                    $params["c_config_shop_currency"],
                    $fetchLanguageId,
                    $host
                )
            );
        } catch (\PDOException $e) {
            $this->setError($e->getMessage());

            return false;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * @param $password
     *
     * @return string
     */
    private function saltPassword($password)
    {
        return md5("A9ASD:_AD!_=%a8nx0asssblPlasS$" . md5($password));
    }

    /**
     * @param $params
     *
     * @return bool
     */
    public function createAdmin($params)
    {
        if (empty($params["c_config_admin_user"]) || empty($params["c_config_admin_name"]) || empty($params["c_config_admin_email"]) || empty($params["c_config_admin_language"]) || empty($params["c_config_admin_password"]) || empty($params["c_config_admin_password2"]) || $params["c_config_admin_password"] != $params["c_config_admin_password2"]
        ) {
            $this->setError("Please fill in all required fields. (admin user)");

            return false;
        }

        // Fetch / convert required data
        try {

            $fetchLanguageId = $this->getDatabase()->prepare("SELECT id FROM s_core_locales WHERE locale = ?");
            $fetchLanguageId->execute(array($params["c_config_admin_language"]));
            $fetchLanguageId = $fetchLanguageId->fetchColumn();
        } catch (\PDOException $e) {
            $this->setError($e->getMessage());

            return false;
        }
        if (!$fetchLanguageId) {
            $this->setError("Could not resolve language " . $params["c_config_admin_language"]);

            return false;
        }

        try {
            // Drop previous inserted admins
            $this->getDatabase()->query("DELETE FROM s_core_auth");

            // Insert new admin
            $prepareStatement = $this->getDatabase()->prepare("
                INSERT INTO s_core_auth (roleID,username,password,localeID,`name`,email,active,admin,salted,lockeduntil)
                VALUES (
                1,?,?,?,?,?,1,1,1,'0000-00-00 00:00:00'
                )
            ");
            $prepareStatement->execute(
                array(
                    $params["c_config_admin_user"],
                    $this->saltPassword($params["c_config_admin_password"]),
                    $fetchLanguageId,
                    $params["c_config_admin_name"],
                    $params["c_config_admin_email"]
                )
            );
        } catch (\PDOException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        return true;
        // Create backend user in s_core_auth
    }

    /**
     * @param $params
     *
     * @return bool
     */
    public function updateConfig($params)
    {
        // Do update on shop-configuration
        if (empty($params["c_config_shopName"]) || empty($params["c_config_mail"])) {
            $this->setError("Please fill in all required fields. (shop configuration#2)");

            return false;
        }

        // Do update on s_core_shops
        try {
            // eMail
            $sql = "
        DELETE FROM s_core_config_values WHERE element_id =
        (SELECT id FROM s_core_config_elements WHERE name='mail')
        AND shop_id = 1
        ";
            $this->getDatabase()->query($sql);

            $sql = "
         INSERT INTO `s_core_config_values` (`id`, `element_id`, `shop_id`, `value`) VALUES
         (NULL, (SELECT id FROM s_core_config_elements WHERE name='mail'), 1, ?);
         ";
            $prepareStatement = $this->getDatabase()->prepare($sql);
            $prepareStatement->execute(array(serialize($params["c_config_mail"])));

            // Shop name
            $sql = "
         DELETE FROM s_core_config_values WHERE element_id =
         (SELECT id FROM s_core_config_elements WHERE name='shopName')
         AND shop_id = 1
         ";
            $this->getDatabase()->query($sql);

            $sql = "
          INSERT INTO `s_core_config_values` (`id`, `element_id`, `shop_id`, `value`) VALUES
          (NULL, (SELECT id FROM s_core_config_elements WHERE name='shopName'), 1, ?);
          ";
            $prepareStatement = $this->getDatabase()->prepare($sql);
            $prepareStatement->execute(array(serialize($params["c_config_shopName"])));
        } catch (\PDOException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getShopDomain()
    {
        $domain = $_SERVER["HTTP_HOST"];
        $basepath = str_replace("/recovery/install/index.php", "", $_SERVER["SCRIPT_NAME"]);

        return array("domain" => $domain, "basepath" => $basepath);
    }

    /**
     * @param $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }
}
