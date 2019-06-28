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

namespace Shopware\Recovery\Install;

/**
 * Example source file:
 *
 * <?xml version="1.0"?>
 * <shopware>
 *   <files>
 *     <file><name>var/log/</name></file>
 *     <file><name>config.php</name></file>
 *   </files>
 * </shopware>
 */
class RequirementsPath
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @var array
     */
    private $files;

    /**
     * @param string $basePath
     * @param string $sourceFile
     */
    public function __construct($basePath, $sourceFile)
    {
        $this->basePath = rtrim($basePath, '/') . '/';
        $this->sourceFile = $sourceFile;

        $this->files = $this->readList($sourceFile);
    }

    public function addFile($file)
    {
        $this->files[] = $file;
    }

    /**
     * @return RequirementsPathResult
     */
    public function check()
    {
        $result = [];

        foreach ($this->files as $file) {
            $entry['name'] = $file;
            $entry['existsAndWriteable'] = $this->checkExits($file);
            $result[] = $entry;
        }

        return new RequirementsPathResult($result);
    }

    /**
     * @param string $sourceFile
     *
     * @return string[]
     */
    private function readList($sourceFile)
    {
        $xml = simplexml_load_file($sourceFile);
        $list = [];
        foreach ($xml->files->file as $file) {
            $list[] = (string) $file->name;
        }

        return $list;
    }

    /**
     * Checks a requirement
     *
     * @param string $name
     *
     * @return bool
     */
    private function checkExits($name)
    {
        $name = $this->basePath . $name;

        return file_exists($name) && is_readable($name) && is_writable($name);
    }
}
