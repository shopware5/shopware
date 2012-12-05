<?php

/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Components
 * @subpackage Check
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */



/**
 * Shopware Check File
 *
 * @todo:all: Doku!
 *
 * {@inheritdoc}
 * <code>
 * $list = new Shopware_Components_Check_File();
 * $data = $list->toArray();
 * </code>
 */
class Shopware_Components_Check_File implements IteratorAggregate, Countable
{
	protected $list;
    protected $testDir = '';
		
	/**
	 * Checks all requirements
	 */
	protected function checkAll()
	{
		foreach ($this->list as $requirement) {
			$requirement->required = Shopware()->Config()->Version;
			$requirement->version = $this->check($requirement);
			$requirement->result = $this->compare(
				$requirement->name,
				$requirement->version,
				$requirement->required
			);
		}
	}
	
	/**
	 * Checks a requirement
	 *
	 * @param object $file
	 * @return bool
	 */
	protected function check($file)
	{
        $filePath = $this->testDir . $file->name;
		if (!file_exists($filePath)) {
			return false;
		}
		$file->hash = md5_file($filePath);
		
		foreach ($file->test as $test) {
			if($test->hash == $file->hash) {
				$file->version = $test->version;
			}
		}
		unset($file->test);
		
		return $file->version;
	}
	
	/**
	 * Compares the requirement with the version
	 *
	 * @param string $name
	 * @param string $version
	 * @param string $required
	 * @return bool
	 */
	protected function compare($name, $version, $required)
	{
		return version_compare($required, $version, '<=');
	}
	
	/**
	 * Returns the check list
	 *
	 * @return Iterator
	 */
	public function getList()
	{
		if($this->list === null) {
			$this->list = new Zend_Config_Xml(
				dirname(__FILE__) . '/Data/File.xml',
				'files',
				true
			);
			$this->list = $this->list->file;
			$this->checkAll();
		}
		return $this->list;
	}
	
	/**
	 * Returns the check list
	 *
	 * @return Iterator
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
    	return $this->getList()->toArray();
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

    public function setTestDir($dir)
    {
        $this->testDir = $dir;
    }
}