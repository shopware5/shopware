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

namespace Shopware\Recovery\Install;

use ErrorException;
use RuntimeException;
use Shopware\Recovery\Install\Service\TranslationService;
use SimpleXMLElement;

class Requirements
{
    /**
     * @var string
     */
    private $sourceFile;

    /**
     * @var string[]
     */
    private $translations;

    /**
     * @param string $sourceFile
     */
    public function __construct($sourceFile, TranslationService $translations)
    {
        if (!is_readable($sourceFile)) {
            throw new RuntimeException(sprintf('Cannot read requirements file in %s.', $sourceFile));
        }

        $this->sourceFile = $sourceFile;

        $this->translations = $translations;
    }

    /**
     *  Returns the check list
     *
     * @return array
     */
    public function toArray()
    {
        $result = [
            'hasErrors' => false,
            'hasWarnings' => false,
            'checks' => [],
            'phpVersionNotSupported' => false,
        ];

        $checks = [];
        foreach ($this->runChecks() as $requirement) {
            $check = [];

            // Skip database checks because we don't have a db connection yet
            if ((bool) $requirement->database) {
                continue;
            }

            $check['name'] = (string) $requirement->name;
            $check['group'] = (string) $requirement->group;
            $check['notice'] = (string) $requirement->notice;
            $check['required'] = (string) $requirement->required;
            $check['version'] = (string) $requirement->version;
            $check['maxCompatibleVersion'] = (string) $requirement->maxCompatibleVersion;
            $check['check'] = (bool) (string) $requirement->result;
            $check['error'] = (bool) $requirement->error;

            if ($check['maxCompatibleVersion'] && $check['check']) {
                $check = $this->handleMaxCompatibleVersion($check);
                if ($check['notice']) {
                    $result['phpVersionNotSupported'] = $check['notice'];
                }
            }

            $checks[] = $check;
        }

        $checks = array_merge($checks, $this->checkOpcache());

        foreach ($checks as $check) {
            if (!$check['check'] && $check['error']) {
                $check['status'] = 'error';
                $result['hasErrors'] = true;
            } elseif (!$check['check']) {
                $check['status'] = 'warning';
                $result['hasWarnings'] = true;
            } else {
                $check['status'] = 'ok';
            }
            unset($check['check'], $check['error']);

            $result['checks'][] = $check;
        }

        return $result;
    }

    /**
     * Returns the check list
     *
     * @return SimpleXMLElement[]
     */
    private function runChecks()
    {
        $xmlObject = simplexml_load_file($this->sourceFile);

        if (!\is_object($xmlObject->requirements)) {
            throw new RuntimeException('Requirements XML file is not valid.');
        }

        foreach ($xmlObject->requirement as $requirement) {
            $name = (string) $requirement->name;
            $value = $this->getRuntimeValue($name);
            $requirement->result = $this->compare(
                $name,
                $value,
                (string) $requirement->required
            );
            $requirement->version = $value;
        }

        return $xmlObject->requirement;
    }

    /**
     * Checks a requirement
     *
     * @param string $name
     *
     * @return bool|string|int|null
     */
    private function getRuntimeValue($name)
    {
        $m = 'check' . str_replace(' ', '', ucwords(str_replace(['_', '.'], ' ', $name)));
        if (method_exists($this, $m)) {
            return $this->$m();
        } elseif (\extension_loaded($name)) {
            return true;
        } elseif (\function_exists($name)) {
            return true;
        } elseif (($value = \ini_get($name)) !== null) {
            if (strtolower($value) == 'off' || (is_numeric($value) && $value == 0)) {
                return false;
            } elseif (strtolower($value) == 'on' || (is_numeric($value) && $value == 1)) {
                return true;
            }

            return $value;
        }

        return null;
    }

    /**
     * Compares the requirement with the version
     *
     * @param string $name
     * @param string $value
     * @param string $requiredValue
     *
     * @return bool
     */
    private function compare($name, $value, $requiredValue)
    {
        $m = 'compare' . str_replace(' ', '', ucwords(str_replace(['_', '.'], ' ', $name)));
        if (method_exists($this, $m)) {
            return $this->$m($value, $requiredValue);
        } elseif (preg_match('#^[0-9]+[A-Z]$#', $requiredValue)) {
            return $this->decodePhpSize($requiredValue) <= $this->decodePhpSize($value);
        } elseif (preg_match('#^[0-9]+ [A-Z]+$#i', $requiredValue)) {
            return $this->decodeSize($requiredValue) <= $this->decodeSize($value);
        } elseif (preg_match('#^[0-9][0-9\.]+$#', $requiredValue)) {
            return version_compare($requiredValue, $value, '<=');
        }

        return $requiredValue == $value;
    }

    /**
     * Checks the php version
     *
     * @return bool|string
     */
    private function checkPhp()
    {
        if (strpos(phpversion(), '-')) {
            return substr(phpversion(), 0, strpos(phpversion(), '-'));
        }

        return phpversion();
    }

    /**
     * Checks the php version
     *
     * @return bool
     */
    private function checkModRewrite()
    {
        return isset($_SERVER['MOD_REWRITE']);
    }

    /**
     * Checks the opcache configuration if the opcache exists.
     */
    private function checkOpcache()
    {
        if (!\extension_loaded('Zend OPcache')) {
            return [];
        }

        $opcacheRequirements = [[
            'name' => 'opcache.use_cwd',
            'group' => 'core',
            'required' => 1,
            'version' => \ini_get('opcache.use_cwd'),
            'result' => \ini_get('opcache.use_cwd'),
            'notice' => '',
            'check' => $this->compare('opcache.use_cwd', \ini_get('opcache.use_cwd'), '1'),
            'error' => '',
        ]];

        try {
            if (fileinode('/') > 2) {
                $opcacheRequirements[] = [
                    'name' => 'opcache.validate_root',
                    'group' => 'core',
                    'required' => 1,
                    'version' => \ini_get('opcache.validate_root'),
                    'result' => \ini_get('opcache.validate_root'),
                    'notice' => '',
                    'check' => $this->compare('opcache.validate_root', \ini_get('opcache.validate_root'), '1'),
                    'error' => '',
                ];
            }
        } catch (ErrorException $x) {
            // Systems that have an 'open_basedir' defined might not allow an access of '/'
        }

        return $opcacheRequirements;
    }

    /**
     * Checks the curl version
     *
     * @return bool|string
     */
    private function checkCurl()
    {
        if (\function_exists('curl_version')) {
            $curl = curl_version();

            return $curl['version'];
        } elseif (\function_exists('curl_init')) {
            return true;
        }

        return false;
    }

    /**
     * Checks the lib xml version
     *
     * @return bool|string
     */
    private function checkLibXml()
    {
        if (\defined('LIBXML_DOTTED_VERSION')) {
            return LIBXML_DOTTED_VERSION;
        }

        return false;
    }

    /**
     * Checks the gd version
     *
     * @return bool|string
     */
    private function checkGd()
    {
        if (\function_exists('gd_info')) {
            $gd = gd_info();
            if (preg_match('#[0-9.]+#', $gd['GD Version'], $match)) {
                if (substr_count($match[0], '.') == 1) {
                    $match[0] .= '.0';
                }

                return $match[0];
            }

            return $gd['GD Version'];
        }

        return false;
    }

    /**
     * Checks the gd jpg support
     *
     * @return bool|string
     */
    private function checkGdJpg()
    {
        if (\function_exists('gd_info')) {
            $gd = gd_info();

            return !empty($gd['JPEG Support']) || !empty($gd['JPG Support']);
        }

        return false;
    }

    /**
     * Checks the freetype support
     *
     * @return bool|string
     */
    private function checkFreetype()
    {
        if (\function_exists('gd_info')) {
            $gd = gd_info();

            return !empty($gd['FreeType Support']);
        }

        return false;
    }

    /**
     * Checks the session save path config
     *
     * @return bool|string
     */
    private function checkSessionSavePath()
    {
        if (\function_exists('session_save_path')) {
            return (bool) session_save_path();
        } elseif (\ini_get('session.save_path')) {
            return true;
        }

        return false;
    }

    /**
     * Checks the suhosin.get.max_value_length which limits the max get parameter length.
     *
     * @return int
     */
    private function checkSuhosinGetMaxValueLength()
    {
        $length = (int) \ini_get('suhosin.get.max_value_length');
        if ($length === 0) {
            return 2000;
        }

        return $length;
    }

    /**
     * Checks the include path config
     *
     * @return bool
     */
    private function checkIncludePath()
    {
        $old = set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . DIRECTORY_SEPARATOR);

        return $old && get_include_path() != $old;
    }

    /**
     * Compare max execution time config
     *
     * @param string $version
     * @param string $required
     *
     * @return bool
     */
    private function compareMaxExecutionTime($version, $required)
    {
        if (!$version) {
            return true;
        }

        return version_compare($required, $version, '<=');
    }

    /**
     * Decode php size format
     *
     * @param string $val
     *
     * @return float
     */
    private function decodePhpSize($val)
    {
        $val = strtolower(trim($val));
        $last = substr($val, -1);

        $val = (float) $val;
        switch ($last) {
            /* @noinspection PhpMissingBreakStatementInspection */
            case 'g':
                $val *= 1024;
                /* @noinspection PhpMissingBreakStatementInspection */
                // no break
            case 'm':
                $val *= 1024;
                // no break
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    /**
     * Decode byte size format
     *
     * @param string $val
     *
     * @return float
     */
    private function decodeSize($val)
    {
        $val = trim($val);
        list($val, $last) = explode(' ', $val);
        $val = (float) $val;
        switch (strtoupper($last)) {
            /* @noinspection PhpMissingBreakStatementInspection */
            case 'TB':
                $val *= 1024;
                // no break
            case 'GB':
                $val *= 1024;
                // no break
            case 'MB':
                $val *= 1024;
                // no break
            case 'KB':
                $val *= 1024;
                // no break
            case 'B':
                $val = (float) $val;
        }

        return $val;
    }

    /**
     * Encode byte size format
     *
     * @param float $bytes
     *
     * @return string
     */
    private function encodeSize($bytes)
    {
        $types = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes >= 1024 && $i < (\count($types) - 1); $bytes /= 1024, $i++) {
        }

        return round($bytes, 2) . ' ' . $types[$i];
    }

    /**
     * @return array
     */
    private function handleMaxCompatibleVersion(array $check)
    {
        if (version_compare($check['version'], $check['maxCompatibleVersion'], '>')) {
            $check['check'] = false;
            $maxCompatibleVersion = str_replace('.99', '', $check['maxCompatibleVersion']);
            $key = 'requirements_php_max_compatible_version';

            $check['notice'] = sprintf($this->translations->translate($key), $maxCompatibleVersion);
        }

        return $check;
    }
}
