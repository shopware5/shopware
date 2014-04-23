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
 * Example source file:
 *
 * <?xml version="1.0"?>
 * <shopware>
 *   <files>
 *     <file><name>logs/</name></file>
 *     <file><name>config.php</name></file>
 *   </files>
 * </shopware>
 *
 * @category  Shopware
 * @package   Shopware\Recovery\Update
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class RequirementsPath implements \IteratorAggregate, \Countable
{
    /**
     * @var
     */
    protected $list;

    /**
     * @var bool
     */
    protected $fatalError = false;

    /**
     * @var string
     */
    private $basePath;

    /**
     * @var string
     */
    private $sourceFile;

    /**
     * @param string $basePath
     * @param string $sourceFile
     */
    public function __construct($basePath, $sourceFile)
    {
        $this->basePath = rtrim($basePath, '/') . '/';
        $this->sourceFile = $sourceFile;
    }

    /**
     * @param bool $fatalError
     */
    public function setFatalError($fatalError)
    {
        $this->fatalError = (bool)$fatalError;
    }

    /**
     * @return bool
     */
    public function getFatalError()
    {
        return $this->fatalError;
    }

    /**
     * Checks all requirements
     */
    protected function checkAll()
    {
        foreach ($this->list->file as $requirement) {
            $requirement->existsAndWriteable = $this->checkExits($requirement->name);
        }
    }

    /**
     * Checks a requirement
     *
     * @param  string $name
     * @return bool
     */
    protected function checkExits($name)
    {
        $name = $this->basePath . $name;

        return (file_exists($name) && is_readable($name) && is_writeable($name));
    }

    /**
     * Returns the check list
     *
     * @return \Iterator
     */
    public function getList()
    {
        if ($this->list === null) {
            $this->list = simplexml_load_file($this->sourceFile);
            $this->list = $this->list->files;
            $this->checkAll();
        }

        return $this->list;
    }

    /**
     * Returns the check list
     *
     * @return \Iterator
     */
    public function getIterator()
    {
        return $this->getList();
    }

    /**
     * Returns the check list
     *
     * @return array
     */
    public function toArray()
    {
        $list = array();
        $getList = $this->getList();
        foreach ($getList->file as $requirement) {
            $listResult = array();

            $listResult["name"] = (string) $requirement->name;
            $listResult["existsAndWriteable"] = (string) $requirement->existsAndWriteable;
            if (empty($listResult["existsAndWriteable"])) {
                $this->setFatalError(true);
            }
            $list[] = $listResult;
        }

        return $list;
    }

    /**
     * Counts the check list
     *
     * @return int
     */
    public function count()
    {
        return $this->getList()->count();
    }
}
