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

/**
 * Shopware Check File
 *
 * <code>
 * $list = new Shopware_Components_Check_File();
 * $data = $list->toArray();
 * </code>
 */
class Shopware_Components_Check_File
{
    /**
     * @var string
     */
    protected $testDir = '';

    /**
     * @var array
     */
    private $skipList;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @param string $filePath
     * @param string $testDir
     * @param array  $skipList List of filenames to be skipped
     */
    public function __construct($filePath, $testDir = null, $skipList = [])
    {
        $this->filePath = $filePath;
        $this->skipList = $skipList;

        if ($testDir !== null) {
            $this->setTestDir($testDir);
        }
    }

    /**
     * Returns the check list
     *
     * @return array
     */
    public function toArray()
    {
        $baseDir = $this->testDir;

        $md5Sums = trim(file_get_contents($this->filePath));
        $md5Sums = explode("\n", $md5Sums);

        $good = [];
        $bad = [];

        foreach ($md5Sums as $row) {
            list($expectedMd5Sum, $file) = explode('  ', trim($row));

            if (in_array($file, $this->skipList)) {
                continue;
            }

            $fileAvailable = is_file($baseDir . $file);

            $md5SumMatch = false;
            if ($fileAvailable) {
                $md5Sum = md5_file($baseDir . $file);
                $md5SumMatch = ($md5Sum === $expectedMd5Sum);
            }

            if ($md5SumMatch) {
                $good[] = [
                    'name' => $file,
                    'available' => $fileAvailable,
                    'result' => $md5SumMatch,
                ];
            } else {
                $bad[] = [
                    'name' => $file,
                    'available' => $fileAvailable,
                    'result' => $md5SumMatch,
                ];
            }
        }

        return $bad + $good;
    }

    /**
     * @param string $dir
     */
    public function setTestDir($dir)
    {
        $this->testDir = $dir;
    }
}
