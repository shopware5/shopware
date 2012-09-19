<?php
class	Shopware_Components_Db_Import_Sql extends Shopware_Components_Db_Import_Abstract
{
	protected $length = 65535;
		
	public function fetch()
	{
		$this->current = '';
		while (!feof($this->stream)) {
			$this->current .= fgets($this->stream, $this->length);
			if(substr(rtrim($this->current), -1) == ';') {
				break;
			}
		}
		$this->current = trim(preg_replace('#^\s*--[^\n\r]*#', '', $this->current));
	}
}