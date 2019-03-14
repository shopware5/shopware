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

namespace Shopware\Recovery\Update;

use Slim\Http\Request;

class Utils
{
    /**
     * @param string $file
     *
     * @return bool
     */
    public static function check($file)
    {
        if (file_exists($file)) {
            if (!is_writable($file)) {
                return $file;
            }

            return true;
        }

        return self::check(dirname($file));
    }

    /**
     * @param string $xmlPath
     *
     * @return array
     */
    public static function getPaths($xmlPath)
    {
        $paths = [];
        $xml = simplexml_load_file($xmlPath);

        foreach ($xml->files->file as $entry) {
            $paths[] = (string) $entry->name;
        }

        return $paths;
    }

    /**
     * @param array  $paths
     * @param string $basePath
     *
     * @return array
     */
    public static function checkPaths($paths, $basePath)
    {
        $results = [];
        foreach ($paths as $path) {
            $name = $basePath . '/' . $path;
            $result = file_exists($name) && is_readable($name) && is_writable($name);
            $results[] = [
                'name' => $path,
                'result' => $result,
            ];
        }

        return $results;
    }

    public static function getRealIpAddr()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    /**
     * @param string $dir
     * @param bool   $includeDir
     */
    public static function deleteDir($dir, $includeDir = false)
    {
        $dir = rtrim($dir, '/') . '/';
        if (!is_dir($dir)) {
            return;
        }

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            /** @var \SplFileInfo $path */
            foreach ($iterator as $path) {
                if ($path->getFilename() === '.gitkeep') {
                    continue;
                }

                $path->isFile() ? @unlink($path->getPathname()) : @rmdir($path->getPathname());
            }
        } catch (\Exception $e) {
            // todo: add error handling
            // empty catch intendded.
        }

        if ($includeDir) {
            @rmdir($dir);
        }
    }

    /**
     * @param string $clientIp
     *
     * @return bool
     */
    public static function isAllowed($clientIp)
    {
        $allowed = trim(file_get_contents(UPDATE_PATH . '/allowed_ip.txt'));
        $allowed = explode("\n", $allowed);
        $allowed = array_map('trim', $allowed);

        return in_array($clientIp, $allowed);
    }

    /**
     * @param string $lang
     *
     * @return string
     */
    public static function getLanguage(Request $request, $lang = null)
    {
        $allowedLanguages = ['de', 'en'];
        $selectedLanguage = 'de';

        if ($lang && in_array($lang, $allowedLanguages)) {
            return $lang;
        }

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $selectedLanguage = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            $selectedLanguage = substr($selectedLanguage[0], 0, 2);
        }

        if (empty($selectedLanguage) || !in_array($selectedLanguage, $allowedLanguages)) {
            $selectedLanguage = 'de';
        }

        if (isset($_POST['language']) && in_array($_POST['language'], $allowedLanguages)) {
            $selectedLanguage = $_POST['language'];
            $_SESSION['language'] = $selectedLanguage;
        } elseif (isset($_SESSION['language']) && in_array($_SESSION['language'], $allowedLanguages)) {
            $selectedLanguage = $_SESSION['language'];
        } else {
            $_SESSION['language'] = $selectedLanguage;
        }

        return $selectedLanguage;
    }

    /**
     * @param string $shopPath
     *
     * @return \PDO
     */
    public static function getConnection($shopPath)
    {
        if (file_exists($shopPath . '/config.php')) {
            $config = require $shopPath . '/config.php';
        } else {
            die('Could not find shopware config');
        }

        $dbConfig = $config['db'];
        if (!isset($dbConfig['host'])) {
            $dbConfig['host'] = 'localhost';
        }

        $dsn = [];
        $dsn[] = 'host=' . $dbConfig['host'];
        $dsn[] = 'dbname=' . $dbConfig['dbname'];

        if (isset($dbConfig['port'])) {
            $dsn[] = 'port=' . $dbConfig['port'];
        }
        if (isset($dbConfig['unix_socket'])) {
            $dsn[] = 'unix_socket=' . $dbConfig['unix_socket'];
        }

        $dsn = 'mysql:' . implode(';', $dsn);

        try {
            $conn = new \PDO(
                $dsn,
                $dbConfig['username'],
                $dbConfig['password'],
                [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"]
            );
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
            exit(1);
        }

        self::setNonStrictSQLMode($conn);
        self::checkSQLMode($conn);

        return $conn;
    }

    /**
     * @param string $dir
     *
     * @return array
     */
    public static function cleanPath($dir)
    {
        $errorFiles = [];

        if (is_file($dir)) {
            try {
                unlink($dir);
            } catch (\ErrorException $e) {
                $errorFiles[$dir] = true;
            }
        } else {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            /** @var \SplFileInfo $path */
            foreach ($iterator as $path) {
                try {
                    if ($path->isDir()) {
                        rmdir($path->__toString());
                    } else {
                        unlink($path->__toString());
                    }
                } catch (\ErrorException $e) {
                    $errorFiles[$dir] = true;
                }
            }

            try {
                rmdir($dir);
            } catch (\ErrorException $e) {
                $errorFiles[$dir] = true;
            }
        }

        return array_keys($errorFiles);
    }

    protected static function setNonStrictSQLMode(\PDO $conn)
    {
        $conn->exec("SET @@session.sql_mode = ''");
    }

    /**
     * @throws \RuntimeException
     */
    private static function checkSQLMode(\PDO $conn)
    {
        $sql = 'SELECT @@SESSION.sql_mode;';
        $result = $conn->query($sql)->fetchColumn(0);

        if (strpos($result, 'STRICT_TRANS_TABLES') !== false || strpos($result, 'STRICT_ALL_TABLES') !== false) {
            throw new \RuntimeException("Database error!: The MySQL strict mode is active ($result). Please consult your hosting provider to solve this problem.");
        }
    }
}
