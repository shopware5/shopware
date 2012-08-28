<?php

/*
  ##############################################################################
  # Plugin for Shopware
  # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  # @version $Id$
  # @copyright:   found in /lic/copyright.txt
  #
  ##############################################################################
 */

abstract class payone_tableBasics {

	protected $_name = self::TABLE_NAME;
	protected $_primary = 'id';

	/**
	 *
	 * @param string $where optional
	 * @return int
	 */
	public function count($where = null) {
		if ($where && !preg_match('/^\s*where/i', $where)) {
			$where = 'where ' . $where;
		}
		return (int) Shopware()->Db()->fetchOne('select count(*) from ' . $this->_name . ($where ? " $where" : ''));
	}

	/**
	 *
	 * @return Zend_Db_Table_Select
	 */
	public function select($withFromPart = Zend_Db_Table_Abstract::SELECT_WITHOUT_FROM_PART) {

		$select = Shopware()->Db()->select();
		$select->from($this->_name, array ());

		return $select;
	}

	public function fetchRow($sql, $bind = array (), $fetchMode = null) {
		return Shopware()->Db()->fetchRow($sql, $bind, $fetchMode);
	}

	/**
	 *
	 * @return array
	 */
	public function tableColumns() {
		return array_keys(Shopware()->Db()->describeTable($this->_name));
	}

	public function insert($bind) {
		return Shopware()->Db()->insert($this->_name, $bind);
	}

	public function update($bind, $where = '') {
		return Shopware()->Db()->update($this->_name, $bind, $where);
	}

}

?>